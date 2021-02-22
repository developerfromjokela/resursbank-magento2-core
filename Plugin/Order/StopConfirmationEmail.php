<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

use Exception;
use Magento\Sales\Model\Order;
use Resursbank\Rco\Helper\Log;

/**
 * Prevents the order confirmation email from being sent upon order creation.
 * The order confirmation email will instead be sent when we receive a callback
 * indicating the payment has been confirmed by Resurs Bank.
 */
class StopConfirmationEmail
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @param Log $log
     */
    public function __construct(
        Log $log
    ) {
        $this->log = $log;
    }

    /**
     * @param Order $subject
     * @param Order $result
     * @return Order
     * @throws Exception
     */
    public function afterBeforeSave(
        Order $subject,
        Order $result
    ): Order {
        try {
            if ($this->isEnabled($subject)) {
                $subject->setCanSendNewEmailFlag(false);
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Check whether or not this plugin should execute.
     *
     * @param Order $order
     * @return bool
     * @throws Exception
     */
    private function isEnabled(
        Order $order
    ): bool {
        return (
            $order->isObjectNew() &&
            !$order->getOriginalIncrementId() &&
            $order->getGrandTotal() > 0
        );
    }
}
