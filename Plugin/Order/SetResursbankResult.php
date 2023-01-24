<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

use Exception;
use Magento\Checkout\Controller\Onepage\Success;
use Magento\Checkout\Controller\Onepage\Failure;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Api\Data\OrderInterface;
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;

/**
 * Marks whether client reached success or failure page in Magento.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetResursbankResult
{
    /**
     * @var Order
     */
    private Order $order;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var PaymentMethods
     */
    private PaymentMethods $paymentMethods;

    /**
     * @param Log $log
     * @param Order $order
     * @param PaymentMethods $paymentMethods
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Log $log,
        Order $order,
        PaymentMethods $paymentMethods
    ) {
        $this->log = $log;
        $this->order = $order;
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @param Success|Failure $subject
     * @param ResultInterface|Redirect|Page $result
     * @return ResultInterface|Redirect|Page
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        $subject,
        $result
    ) {
        try {
            $order = $this->order->resolveOrderFromRequest();

            if ($this->isEnabled($order)) {
                $this->order->setResursbankResult(
                    $order,
                    ($subject instanceof Success)
                );
            }
        } catch (Exception $e) {
            $this->log->exception($e);
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
            $this->paymentMethods->isResursBankOrder($order) &&
            $this->order->getResursbankResult($order) === null
        );
    }
}
