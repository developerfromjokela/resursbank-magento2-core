<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

use Exception;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\AuthorizeCommand;
use Magento\Payment\Helper\Data as PaymentHelper;
use Resursbank\Core\Helper\Log;

/**
 * Change order state to "new" and status to "pending_payment" after authorize
 * command has executed.
 *
 * We require this because the authorization operation will overwrite the
 * initial status specified in our payment methods configuration, using
 * "processing" as both state and status.
 */
class SetInitialStateStatus
{
    /**
     * @var PaymentHelper
     */
    private PaymentHelper $paymentHelper;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Log $log
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Log $log
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->log = $log;
    }

    /**
     * @param AuthorizeCommand $subject
     * @param Phrase $result
     * @param OrderPaymentInterface $payment
     * @param mixed $amount
     * @param OrderInterface $order
     * @return Phrase
     */
    public function afterExecute(
        AuthorizeCommand $subject,
        Phrase $result,
        OrderPaymentInterface $payment,
        $amount,
        OrderInterface $order
    ): Phrase {
        try {
            $status = $this->paymentHelper
                ->getMethodInstance($payment->getMethod())
                ->getConfigData('order_status');

            if (!is_string($status) || $status === '') {
                $status = 'pending_payment';
            }

            $order->setState(Order::STATE_NEW)->setStatus($status);
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
