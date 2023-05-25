<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Command\Authorize;

use Codeception\Step\Meta;
use Exception;
use JsonException;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\PaymentException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;
use ReflectionException;
use Resursbank\Core\Exception\PaymentDataException;
use Resursbank\Core\Helper\Callback as CoreCallback;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Url;
use Resursbank\Core\Model\Api\Payment\Converter\Item\DiscountItem;
use Resursbank\Core\Model\Api\Payment\Converter\Item\ItemInterface;
use Resursbank\Core\Model\Api\Payment\Converter\Item\ShippingItem;
use Resursbank\Core\Model\Payment\Resursbank;
use Resursbank\Core\Model\Api\Payment\Converter\QuoteConverter;
use Resursbank\Core\ViewModel\Session\Checkout;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Address as EcomAddress;
use Resursbank\Ecom\Lib\Model\Callback\Enum\CallbackType;
use Resursbank\Ecom\Lib\Model\Payment as EcomPayment;
use Resursbank\Ecom\Lib\Model\Payment\Customer as CustomerModel;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Model\Payment\Metadata;
use Resursbank\Ecom\Lib\Model\Payment\Metadata\Entry;
use Resursbank\Ecom\Lib\Model\Payment\Metadata\EntryCollection;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Options;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Options\Callback;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Options\Callbacks;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Options\ParticipantRedirectionUrls;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Options\RedirectionUrls;
use Resursbank\Ecom\Module\Payment\Repository as PaymentRepository;
use Throwable;

use function get_class;

/**
 * Payment authorization command for MAPI.
 *
 * @noinspection EfferentObjectCouplingInspection
 */
class Mapi
{
    /**
     * @param Config $config
     * @param Url $url
     * @param Checkout $session
     * @param QuoteConverter $quoteConverter
     * @param Session $customerSession
     */
    public function __construct(
        private readonly Config $config,
        private readonly Url $url,
        private readonly Checkout $session,
        private readonly QuoteConverter $quoteConverter,
        private readonly Session $customerSession,
        private readonly CoreCallback $callback
    ) {
    }

    /**
     * Create payment using MAPI.
     *
     * @param Payment $payment
     * @param string $store
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws PaymentDataException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function createPayment(Payment $payment, string $store): void
    {
        $ecomPayment = PaymentRepository::create(
            storeId: $this->config->getStore(scopeCode: $store),
            paymentMethodId: str_replace(
                search: Resursbank::CODE_PREFIX,
                replace: '',
                subject: $payment->getMethod()
            ),
            orderLines: $this->getOrderLines(),
            orderReference: (string) $payment->getOrder()->getIncrementId(),
            customer: $this->getCustomerData(payment: $payment),
            metadata: $this->getCustomerMetadata(),
            options: $this->getOptions(order: $payment->getOrder())
        );

        if (!$ecomPayment->isProcessable()) {
            throw new PaymentDataException(
                phrase: __('Payment is not processable.')
            );
        }

        $this->session->setData(
            'resursbank_simplified_signing_url',
            $ecomPayment->taskRedirectionUrls?->customerUrl ?? $this->url->getSuccessUrl(
                quoteId: (int) $payment->getOrder()->getQuoteId()
            )
        );

        $payment->setTransactionId(transactionId: $ecomPayment->id)
            ->setIsTransactionClosed(isClosed: false);
    }

    /**
     * Get customer metadata.
     *
     * @return Metadata|null
     * @throws IllegalTypeException
     */
    private function getCustomerMetadata(): ?Metadata
    {
        $result = null;

        if ($this->customerSession->isLoggedIn()) {
            $result = new Metadata(
                custom: new EntryCollection(data: [
                    new Entry(
                        key: 'externalCustomerId',
                        value: (string) $this->customerSession->getCustomerId()
                    ),
                ])
            );
        }

        return $result;
    }

    /**
     * Get order lines.
     *
     * @throws NoSuchEntityException
     * @throws IllegalValueException
     * @throws LocalizedException
     * @throws IllegalTypeException
     * @throws Exception
     */
    private function getOrderLines(): OrderLineCollection
    {
        $items = $this->quoteConverter->convert(entity: $this->session->getQuote());

        $data = [];

        /** @var ItemInterface $item */
        foreach ($items as $item) {
            $data[] = new OrderLine(
                quantity: $item->getQuantity(),
                quantityUnit: (string) __('rb-default-quantity-unit'),
                vatRate: $item->getVatPct(),
                totalAmountIncludingVat: $item->getTotalAmountInclVat(),
                description: $item->getDescription(),
                reference: $item->getArtNo(),
                type: match ($item->getType()) {
                    'DISCOUNT' =>  OrderLineType::DISCOUNT,
                    'SHIPPING_FEE' => OrderLineType::SHIPPING,
                    default => OrderLineType::NORMAL
                }
            );
        }

        return new OrderLineCollection(data: $data);
    }

    /**
     * Resolve customer data.
     *
     * @param Payment $payment
     * @return CustomerModel
     * @throws IllegalCharsetException
     * @throws IllegalValueException
     */
    private function getCustomerData(Payment $payment): CustomerModel
    {
        $address = $payment->getOrder()->getShippingAddress();

        if ($address === null) {
            $address = $payment->getOrder()->getBillingAddress();
        }

        $isCompany = (bool) $this->session->getData(
            key: 'resursbank_simplified_is_company'
        );

        $govId = (string) $this->session->getData(
            key: 'resursbank_simplified_government_id'
        );

        $contactPerson = $isCompany
            ? $address->getFirstname() . ' ' . $address->getLastname()
            : '';

        return new CustomerModel(
            deliveryAddress: $this->getDeliveryAddress(
                address: $address,
                isCompany: $isCompany
            ),
            customerType: $isCompany ? CustomerType::LEGAL : CustomerType::NATURAL,
            contactPerson: $contactPerson,
            email: $address->getEmail(),
            governmentId: $govId,
            mobilePhone: $address->getTelephone(),
            deviceInfo: new DeviceInfo(
                ip: $_SERVER['REMOTE_ADDR'] ?? '',
                userAgent: $_SERVER['HTTP_USER_AGENT'] ?? ''
            )
        );
    }

    /**
     * Resolve delivery address information.
     *
     * @param Address $address
     * @param bool $isCompany
     * @return EcomAddress
     * @throws IllegalValueException
     * @throws IllegalCharsetException
     */
    private function getDeliveryAddress(
        Address $address,
        bool $isCompany
    ): EcomAddress {
        $fullName = $isCompany
            ? $address->getCompany()
            : $address->getFirstname() . ' ' . $address->getLastname();

        return new EcomAddress(
            addressRow1: $address->getStreetLine(number: 1),
            addressRow2: $address->getStreetLine(number: 2),
            postalArea: $address->getCity(),
            postalCode: $address->getPostcode(),
            countryCode: CountryCode::from(value: $address->getCountryId()),
            fullName: $fullName,
            firstName: $address->getFirstname(),
            lastName: $address->getLastname()
        );
    }

    /**
     * Resolve payment options object based on Magento Order.
     *
     * @param OrderInterface $order
     * @return Options
     * @throws IllegalValueException
     */
    private function getOptions(
        OrderInterface $order
    ): Options {
        return new Options(
            initiatedOnCustomersDevice: true,
            handleManualInspection: false,
            handleFrozenPayments: true,
            redirectionUrls: new RedirectionUrls(
                customer: new ParticipantRedirectionUrls(
                    failUrl: $this->url->getFailureUrl(
                        quoteId: (int) $order->getQuoteId()
                    ),
                    successUrl: $this->url->getSuccessUrl(
                        quoteId: (int) $order->getQuoteId()
                    )
                ),
                coApplicant: null,
                merchant: null
            ),
            callbacks: new Callbacks(
                authorization: new Callback(
                    url: $this->callback->getUrl(
                        store: $order->getStore(),
                        type: strtolower(
                            string: CallbackType::AUTHORIZATION->value
                        )
                    )
                ),
                management: null
            )
        );
    }
}
