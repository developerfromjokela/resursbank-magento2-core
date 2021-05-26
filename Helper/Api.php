<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use InvalidArgumentException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\ValidatorException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Helper\Api\Credentials as CredentialsHelper;
use Resursbank\Core\Model\Api\Address as ApiAddress;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\Core\Model\Api\Customer;
use Resursbank\Core\Model\Api\Payment as PaymentModel;
use Resursbank\RBEcomPHP\RESURS_ENVIRONMENTS;
use Resursbank\RBEcomPHP\ResursBank;
use ResursException;
use stdClass;

/**
 * API adapter utilising the EComPHP library.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @noinspection EfferentObjectCouplingInspection
 */
class Api extends AbstractHelper
{
    /**
     * @var CredentialsHelper
     */
    private $credentialsHelper;

    /**
     * @var Order
     */
    private $orderHelper;

    /**
     * @var Version
     */
    private $version;

    /**
     * @param Context $context
     * @param CredentialsHelper $credentialsHelper
     * @param Order $orderHelper
     * @param Version $version
     */
    public function __construct(
        Context $context,
        CredentialsHelper $credentialsHelper,
        Order $orderHelper,
        Version $version
    ) {
        $this->credentialsHelper = $credentialsHelper;
        $this->orderHelper = $orderHelper;
        $this->version = $version;

        parent::__construct($context);
    }

    /**
     * @param Credentials $credentials
     * @return ResursBank
     * @throws Exception
     */
    public function getConnection(
        Credentials $credentials
    ): ResursBank {
        $user = $credentials->getUsername();
        $pass = $credentials->getPassword();
        $env = $credentials->getEnvironment();

        // Validate API credentials & settings.
        if ($user === null || $pass === null || $env === null) {
            throw new InvalidArgumentException(
                'Failed to establish API connection, incomplete Credentials.'
            );
        }

        // Establish API connection.
        $connection = new ResursBank($user, $pass, $env);

        // Enable WSDL cache to suppress redundant API calls.
        $connection->setWsdlCache(true);

        // Enable usage of PSP methods.
        $connection->setSimplifiedPsp(true);

        // Supply API call with debug information.
        $connection->setUserAgent($this->getUserAgent());

        // Deactivate auto debitable types.
        $connection->setAutoDebitableTypes(false);

        return $connection;
    }

    /**
     * Retrieve payment from Resurs Bank corresponding to Magento order.
     *
     * @param OrderInterface $order
     * @return stdClass|null
     * @throws InvalidDataException
     * @throws ResursException
     * @throws ValidatorException
     */
    public function getPayment(
        OrderInterface $order
    ): ?stdClass {
        $payment = null;

        try {
            $payment = $this->getConnection(
                $this->getCredentialsFromOrder($order)
            )->getPayment(
                $this->orderHelper->getIncrementId($order)
            );

            if (!($payment instanceof stdClass)) {
                throw new ValidatorException(
                    __('Unexpected response from ECom.')
                );
            }
        } catch (Exception $e) {
            // If there is no payment we will receive an Exception from ECom.
            if (!$this->validateMissingPaymentException($e)) {
                throw $e;
            }
        }

        return $payment;
    }

    /**
     * Validate that an Exception was thrown because a payment was actually
     * missing.
     *
     * @param Exception $error
     * @return bool
     */
    public function validateMissingPaymentException(
        Exception $error
    ): bool {
        return (
            $error->getCode() === 3 ||
            $error->getCode() === 8
        );
    }

    /**
     * Makes a request to Resurs Bank's API to check if a payment exists for
     * an order at Resurs Bank.
     *
     * If you're planning on using the payment afterwards it's better to use
     * @link getPayment and check the return value. That way you won't waste
     * time making an extra request to the API.
     *
     * @param OrderInterface $order
     * @return bool
     * @throws InvalidDataException
     * @throws ResursException
     * @throws ValidatorException
     */
    public function paymentExists(
        OrderInterface $order
    ): bool {
        return $this->getPayment($order) !== null;
    }

    /**
     * Retrieve API credentials based on order data.
     *
     * @param OrderInterface $order
     * @return Credentials
     * @throws ValidatorException
     */
    public function getCredentialsFromOrder(
        OrderInterface $order
    ): Credentials {
        $credentials = $this->credentialsHelper->resolveFromConfig(
            (string) $order->getStoreId(),
            ScopeInterface::SCOPE_STORES
        );

        /** @phpstan-ignore-next-line */
        $env = (bool) $order->getData('resursbank_is_test');

        $credentials->setEnvironment(
            $env ? RESURS_ENVIRONMENTS::TEST : RESURS_ENVIRONMENTS::PRODUCTION
        );

        return $credentials;
    }

    /**
     * Creates payment model data from a generic object. Expects the generic
     * object to have the same properties as payment data fetched from the API,
     * but it's not required to. Missing properties will be created using
     * default values.
     *
     * @param bool|null $isCompany
     * @param stdClass $payment
     * @return PaymentModel
     */
    public function toPayment(
        stdClass $payment,
        bool $isCompany = null
    ): PaymentModel {
        $paymentId = '';

        if (property_exists($payment, 'paymentId')) {
            $paymentId = (string) $payment->paymentId;
        } elseif (property_exists($payment, 'id')) {
            $paymentId = (string) $payment->id;
        }

        return new PaymentModel(
            $paymentId,
            property_exists(
                $payment,
                'bookPaymentStatus'
            ) ?
                (string) $payment->bookPaymentStatus :
                '',
            property_exists($payment, 'approvedAmount') ?
                (float) $payment->approvedAmount :
                0.0,
            property_exists($payment, 'signingUrl') ?
                (string) $payment->signingUrl :
                '',
            (
                property_exists($payment, 'customer') &&
                $payment->customer instanceof stdClass
            ) ?
                $this->toCustomer(
                    $payment->customer,
                    $isCompany
                ) :
                new Customer(),
        );
    }

    /**
     * Creates customer model data from a generic object. Expects the generic
     * object to have the same properties as customer data fetched from the API,
     * but it's not required to. Missing properties will be created using
     * default values.
     *
     * @param bool|null $isCompany
     * @param stdClass $customer
     * @return Customer
     */
    public function toCustomer(
        stdClass $customer,
        bool $isCompany = null
    ): Customer {
        $phone = property_exists($customer, 'phone') ?
            (string) $customer->phone :
            '';

        return new Customer(
            property_exists($customer, 'governmentId') ?
                (string) $customer->governmentId :
                '',
            $phone,
            property_exists($customer, 'email') ?
                (string) $customer->email :
                '',
            property_exists($customer, 'type') ?
                (string) $customer->type :
                '',
            property_exists($customer, 'address') ?
                $this->toAddress($customer->address, $isCompany, $phone) :
                null
        );
    }

    /**
     * Creates address model data from a generic object. Expects the generic
     * object to have the same properties as address data fetched from the API,
     * but it's not required to. Missing properties will be created using
     * default values.
     *
     * @param object $address
     * @param bool|null $isCompany
     * @param string $telephone
     * @return ApiAddress
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function toAddress(
        object $address,
        bool $isCompany = null,
        string $telephone = ''
    ): ApiAddress {
        return new ApiAddress(
            (
                $isCompany === null &&
                property_exists($address, 'fullName') &&
                (string) $address->fullName !== ''
            ) || $isCompany,
            property_exists($address, 'fullName') ?
                (string) $address->fullName :
                '',
            property_exists($address, 'firstName') ?
                (string) $address->firstName :
                '',
            property_exists($address, 'lastName') ?
                (string) $address->lastName :
                '',
            property_exists($address, 'addressRow1') ?
                (string) $address->addressRow1 :
                '',
            property_exists($address, 'addressRow2') ?
                (string) $address->addressRow2 :
                '',
            property_exists($address, 'postalArea') ?
                (string) $address->postalArea :
                '',
            property_exists($address, 'postalCode') ?
                (string) $address->postalCode :
                '',
            property_exists($address, 'country') ?
                (string) $address->country :
                '',
            $telephone
        );
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return sprintf(
            'Magento 2 | Resursbank_Core %s',
            $this->version->getComposerVersion('Resursbank_Core')
        );
    }
}
