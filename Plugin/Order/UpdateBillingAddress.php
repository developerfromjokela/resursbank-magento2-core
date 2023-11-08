<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

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
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\Model\Api\Payment as PaymentModel;
use Throwable;

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
     * @param Log $log
     * @param AddressRepository $addressRepository
     * @param Order $order
     * @param Api $api
     * @param PaymentMethods $paymentMethods
     */
    public function __construct(
        private readonly Log $log,
        private readonly AddressRepository $addressRepository,
        private readonly Order $order,
        private readonly Api $api,
        private readonly PaymentMethods $paymentMethods
    ) {
    }

    /**
     * Perform billing address update.
     *
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

            if ($this->isEnabled(order: $order)) {
                $paymentData = $this->api->getPayment(order: $order);

                if ($paymentData === null) {
                    throw new InvalidDataException(phrase: __(
                        'Payment data does not exist for ' .
                        $this->order->getIncrementId(order: $order)
                    ));
                }

                $payment = $this->api->toPayment(payment: $paymentData);
                $this->overrideBillingAddress(payment: $payment, order: $order);
            }
        } catch (Throwable $e) {
            $this->log->exception(error: $e);
        }

        return $result;
    }

    /**
     * Check if this plugin is enabled.
     *
     * @param OrderInterface $order
     * @return bool
     */
    private function isEnabled(OrderInterface $order): bool
    {
        return (
            $this->paymentMethods->isResursBankOrder(order: $order) &&
            $this->order->getResursbankResult(order: $order) === null
        );
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
                $billingAddress->setCompany(company: $paymentAddress->getFullName());
            } else {
                $billingAddress
                    ->setFirstname(firstname: $paymentAddress->getFirstName())
                    ->setLastname(lastname: $paymentAddress->getLastName());
            }

            $billingAddress
                ->setStreet(street: [
                    $paymentAddress->getAddressRow1(),
                    $paymentAddress->getAddressRow2()
                ])
                ->setPostcode(postcode: $paymentAddress->getPostalCode())
                ->setCity(city: $paymentAddress->getPostalArea())
                ->setCountryId(id: $paymentAddress->getCountry());

            $this->addressRepository->save(entity: $billingAddress);

            // Ensure the address is applied on the order entity (without
            // this "bill to name" in the order grid would for example give the
            // previous value).
            $order->setBillingAddress($billingAddress);
        }
    }
}
