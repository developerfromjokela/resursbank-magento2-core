<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Command;

use Codeception\Step\Meta;
use Exception;
use JsonException;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\PaymentException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;
use ReflectionException;
use Resursbank\Core\Gateway\Command\Authorize\Mapi;
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
 * Payment authorization command.
 */
class Authorize implements CommandInterface
{
    /**
     * @param Log $log
     * @param Mapi $mapi
     */
    public function __construct(
        private readonly Log $log,
        private readonly Mapi $mapi,
        private readonly Config $config
    ) {
    }

    /**
     * Extended by other API implementations, only transaction id handling is
     * centralized.
     *
     * @param array $commandSubject
     * @return ResultInterface|null
     * @throws PaymentException
     */
    public function execute(
        array $commandSubject
    ): ?ResultInterface {
        try {
            $data = SubjectReader::readPayment(subject: $commandSubject);
            $payment = $data->getPayment();

            if ($payment instanceof Payment) {
                $store = $payment->getOrder()->getStore()->getCode();

                if ($this->config->isMapiActive(scopeCode: $store)) {
                    $this->mapi->createPayment(payment: $payment, store: $store);
                    return null;
                }
            }
        } catch (Throwable $error) {
            $this->log->exception(error: $error);

            throw new PaymentException(phrase: __(
                'Something went wrong when trying to place the order. ' .
                'Please try again, or select another payment method. You ' .
                'could also try refreshing the page.'
            ));
        }

        try {
            if ($payment instanceof Payment) {
                $payment->setTransactionId(
                    transactionId: $data->getOrder()
                        ->getOrderIncrementId()
                )
                    ->setIsTransactionClosed(isClosed: false);
            }
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return null;
    }
}
