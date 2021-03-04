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
     * @param Context $context
     * @param CredentialsHelper $credentialsHelper
     */
    public function __construct(
        Context $context,
        CredentialsHelper $credentialsHelper
    ) {
        $this->credentialsHelper = $credentialsHelper;

        parent::__construct($context);
    }

    /**
     * @param Credentials $credentials
     * @param string $userAgent
     * @return ResursBank
     * @throws Exception
     */
    public function getConnection(
        Credentials $credentials,
        string $userAgent = ''
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
        $connection->setUserAgent($this->getUserAgent($userAgent));

        // Deactivate auto debitable types.
        $connection->setAutoDebitableTypes(false);

        return $connection;
    }

    /**
     * Retrieve payment from Resurs Bank corresponding to Magento order.
     *
     * @param OrderInterface $order
     * @return stdClass
     * @throws ValidatorException
     * @throws ResursException
     * @throws Exception
     */
    public function getPayment(
        OrderInterface $order
    ): stdClass {
        $payment = $this->getConnection($this->getCredentialsFromOrder($order))
            ->getPayment($order->getIncrementId());

        if (!($payment instanceof stdClass)) {
            throw new ValidatorException(__('Unexpected response from ECom.'));
        }

        return $payment;
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
            (string) $order->getStoreId()
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
            property_exists($payment, 'customer') ?
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
        return new Customer(
            property_exists($customer, 'governmentId') ?
                (string) $customer->governmentId :
                '',
            property_exists($customer, 'phone') ?
                (string) $customer->phone :
                '',
            property_exists($customer, 'email') ?
                (string) $customer->email :
                '',
            property_exists($customer, 'type') ?
                (string) $customer->type :
                '',
            property_exists($customer, 'address') ?
                $this->toAddress($customer->address, $isCompany) :
                null
        );
    }

    /**
     * Creates address model data from a generic object. Expects the generic
     * object to have the same properties as address data fetched from the API,
     * but it's not required to. Missing properties will be created using
     * default values.
     *
     * @param bool|null $isCompany
     * @param object $address
     * @return ApiAddress
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function toAddress(
        object $address,
        bool $isCompany = null
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
                ''
        );
    }

    /**
     * @param string $custom
     * @return string
     */
    private function getUserAgent(
        string $custom = ''
    ): string {
        return $custom === '' ? 'Mage 2' : "Mage 2 + ${custom}";
    }
}
