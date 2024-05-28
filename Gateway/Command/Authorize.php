<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Command;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\PaymentException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Resursbank\Core\Gateway\Command;
use Resursbank\Core\Helper\Log;
use Throwable;

/**
 * Payment authorization command.
 */
class Authorize extends Command implements CommandInterface
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
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute(
        array $commandSubject
    ): ?ResultInterface {
        // Resolve outside try-catch to propagate PaymentException.
        $payment = $this->getPaymentFromCommandSubject(commandSubject: $commandSubject);
        $order = $this->getOrder(commandSubject: $commandSubject);

        try {
            $payment->setTransactionId(
                transactionId: $order->getIncrementId()
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
    public function getPaymentFromCommandSubject(
        array $commandSubject
    ): Payment {
        try {
            $data = SubjectReader::readPayment(subject: $commandSubject);
            $payment = $data->getPayment();

            if (!$payment instanceof Payment) {
                throw new PaymentException(phrase: __(
                    'Payment object is not an instance of %1',
                    Payment::class
                ));
            }

            return $payment;
        } catch (Throwable $error) {
            $this->log->exception(error: $error);

            throw new PaymentException(phrase: __(
                'Something went wrong when trying to place the order. ' .
                'Please try again, or select another payment method. You ' .
                'could also try refreshing the page.'
            ));
        }
    }
}
