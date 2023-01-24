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
use Resursbank\Core\Helper\PaymentMethods;

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
     * @var PaymentMethods
     */
    private PaymentMethods $paymentMethods;

    /**
     * @param PaymentHelper $paymentHelper
     * @param PaymentMethods $paymentMethods
     * @param Log $log
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        PaymentMethods $paymentMethods,
        Log $log
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->paymentMethods = $paymentMethods;
        $this->log = $log;
    }

    /**
     * @param AuthorizeCommand $subject
     * @param Phrase $result
     * @param OrderPaymentInterface $payment
     * @param mixed $amount
     * @param OrderInterface $order
     * @return Phrase
     * @noinspection PhpUnusedParameterInspection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        AuthorizeCommand $subject,
        Phrase $result,
        OrderPaymentInterface $payment,
        $amount,
        OrderInterface $order
    ): Phrase {
        try {
            if (!$this->paymentMethods->isResursBankMethod($payment->getMethod())) {
                return $result;
            }

            $status = $this->paymentHelper
                ->getMethodInstance($payment->getMethod())
                ->getConfigData('order_status');

            if (!is_string($status) || $status === '') {
                $status = 'pending_payment';
            }

            /*
             * Magento will later validate and overwrite the status based on
             * what statuses are allowed for which states. In order to use
             * "pending_payment" as order status, the state most be the same, it
             * will otherwise be overwritten with "pending". See
             * vendor/magento/module-sales/Model/Order/Payment.php :: place()
             */
            $order->setState(
                $status === 'pending_payment' ? $status : Order::STATE_NEW
            )->setStatus($status);
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
