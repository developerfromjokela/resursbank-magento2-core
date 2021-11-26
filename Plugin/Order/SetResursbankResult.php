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
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Helper\Log;

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
     * @param Log $log
     * @param Order $order
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Log $log,
        Order $order
    ) {
        $this->log = $log;
        $this->order = $order;
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

            if ($this->order->getResursbankResult($order) === null) {
                $this->order->setResursbankResult(
                    $this->order->resolveOrderFromRequest(),
                    ($subject instanceof Success)
                );
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
