<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Command;

use Magento\Framework\Exception\PaymentException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Resursbank\Core\Helper\Log;
use Throwable;

/**
 * Payment authorization command.
 */
class Authorize implements CommandInterface
{
    /**
     * @param Log $log
     */
    public function __construct(
        private readonly Log $log
    ) {
    }

    /**
     * Execute
     *
     * Note: Extended by other API implementations, only transaction id handling
     * is centralized.
     *
     * @param array $commandSubject
     * @return ResultInterface|null
     * @throws PaymentException
     */
    public function execute(
        array $commandSubject
    ): ?ResultInterface {
        // Resolve outside try-catch to propagate PaymentException.
        $payment = $this->getPayment(commandSubject: $commandSubject);
        $order = $this->getOrder(commandSubject: $commandSubject);

        try {
            $payment->setTransactionId(
                transactionId: $order->getOrderIncrementId()
            )->setIsTransactionClosed(isClosed: false);
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return null;
    }

    /**
     * Resolve Payment from subject data.
     *
     * @param array $commandSubject
     * @return Payment
     * @throws PaymentException
     */
    public function getPayment(
        array $commandSubject
    ): Payment {
        try {
            $data = SubjectReader::readPayment(subject: $commandSubject);
            $payment = $data->getPayment();

            if (!$payment instanceof Payment) {
                throw new PaymentException(
                    phrase: __(
                        'rb-payment-object-not-instance-of',
                        Payment::class
                    )
                );
            }

            return $payment;
        } catch (Throwable $error) {
            $this->log->exception(error: $error);

            throw new PaymentException(phrase: __(
                'rb-placeorder-went-wrong-try-again'
            ));
        }
    }

    /**
     * Resolve Order from subject data.
     *
     * @param array $commandSubject
     * @return OrderAdapterInterface
     * @throws PaymentException
     */
    public function getOrder(
        array $commandSubject
    ): OrderAdapterInterface {
        try {
            $data = SubjectReader::readPayment(subject: $commandSubject);
            $order = $data->getOrder();

            if (!$order instanceof OrderAdapterInterface) {
                throw new PaymentException(
                    phrase: __(
                        'rb-order-object-not-instance-of',
                        OrderAdapterInterface::class
                    )
                );
            }

            return $order;
        } catch (Throwable $error) {
            $this->log->exception(error: $error);

            throw new PaymentException(phrase: __(
                'rb-placeorder-went-wrong-try-again'
            ));
        }
    }
}
