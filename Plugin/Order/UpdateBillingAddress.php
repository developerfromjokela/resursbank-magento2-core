<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

use Exception;
use Magento\Checkout\Controller\Onepage\Success;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\AddressRepository;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Model\Api\Payment as PaymentModel;

/**
 * When the order has been placed and the payment is booked, retrieve the
 * payment from Resurs Bank, and update the billing address on the order in
 * Magento to reflect the address on the payment.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateBillingAddress
{
    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var AddressRepository
     */
    private AddressRepository $addressRepository;

    /**
     * @var Order
     */
    private Order $order;
    /**
     * @var Api
     */
    private Api $api;

    /**
     * @param Log $log
     * @param AddressRepository $addressRepository
     * @param Order $order
     * @param Api $api
     */
    public function __construct(
        Log $log,
        AddressRepository $addressRepository,
        Order $order,
        Api $api
    ) {
        $this->log = $log;
        $this->addressRepository = $addressRepository;
        $this->order = $order;
        $this->api = $api;
    }

    /**
     * NOTE: Since this isn't a crucial operation we will log and ignore
     * potential Exceptions.
     *
     * @param Success $subject
     * @param ResultInterface $result
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterExecute(
        Success $subject,
        ResultInterface $result
    ): ResultInterface {
        /** @noinspection BadExceptionsProcessingInspection */
        try {
            $order = $this->order->resolveOrderFromRequest();

            $paymentData = $this->api->getPayment($order);

            if ($paymentData === null) {
                throw new InvalidDataException(__(
                    'Payment data does not exist for ' .
                    $this->order->getIncrementId($order)
                ));
            }

            $payment = $this->api->toPayment($paymentData);
            $this->overrideBillingAddress($payment, $order);
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Override billing address on order with information from Resurs Bank
     * payment. This is to ensure the customer's billing address in Magento
     * matches the address resolved by Resurs Bank when the payment is created.
     *
     * @param PaymentModel $payment
     * @param OrderInterface $order
     * @return void
     * @throws CouldNotSaveException
     */
    private function overrideBillingAddress(
        PaymentModel $payment,
        OrderInterface $order
    ): void {
        $billingAddress = $order->getBillingAddress();
        $paymentAddress = $payment->getCustomer()->getAddress();

        if ($billingAddress instanceof OrderAddressInterface) {
            if ($payment->getCustomer()->isCompany()) {
                $billingAddress->setCompany($paymentAddress->getFullName());
            } else {
                $billingAddress
                    ->setFirstname($paymentAddress->getFirstName())
                    ->setLastname($paymentAddress->getLastName());
            }

            $billingAddress
                ->setStreet([
                    $paymentAddress->getAddressRow1(),
                    $paymentAddress->getAddressRow2()
                ])
                ->setPostcode($paymentAddress->getPostalCode())
                ->setCity($paymentAddress->getPostalArea())
                ->setCountryId($paymentAddress->getCountry());

            $this->addressRepository->save($billingAddress);

            // Ensure the address is applied on the order entity (without
            // this "bill to name" in the order grid would for example give the
            // previous value).
            $order->setBillingAddress($billingAddress);
        }
    }
}
