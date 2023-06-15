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
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Resursbank\Core\Gateway\Command\Authorize\Mapi;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Throwable;

/**
 * Payment authorization command.
 */
class Authorize implements CommandInterface
{
    /**
     * @param Log $log
     * @param Mapi $mapi
     * @param Config $config
     */
    public function __construct(
        private readonly Log $log,
        private readonly Mapi $mapi,
        private readonly Config $config
    ) {
    }

    /**
     * Execute
     *
     * Note: Extended by other API implementations, only transaction id handling is
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
