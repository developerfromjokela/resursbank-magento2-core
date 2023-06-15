<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Order as OrderHelper;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\RBEcomPHP\ResursBank;

/**
 * Applies a value to the column "resursbank_is_test" to reflect whether a
 * payment was created in the test or production environment.
 */
class SetEnvironmentFlag
{
    /**
     * @var OrderHelper
     */
    private OrderHelper $orderHelper;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var PaymentMethods
     */
    private PaymentMethods $paymentMethods;

    /**
     * @param OrderHelper $orderHelper
     * @param Config $config
     * @param Log $log
     * @param PaymentMethods $paymentMethods
     */
    public function __construct(
        OrderHelper $orderHelper,
        Config $config,
        Log $log,
        PaymentMethods $paymentMethods
    ) {
        $this->orderHelper = $orderHelper;
        $this->config = $config;
        $this->log = $log;
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * Set resursbank_is_test flag
     *
     * @param Order $subject
     * @param Order $result
     * @return Order
     */
    public function afterBeforeSave(
        Order $subject,
        Order $result
    ): Order {
        try {
            if ($this->isEnabled($subject)) {
                $result->setData(
                    'resursbank_is_test',
                    $this->isTestEnvironment($subject)
                );
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Check whether this plugin is enabled.
     *
     * @param Order $order
     * @return bool
     */
    private function isEnabled(Order $order): bool
    {
        return (
            $this->paymentMethods->isResursBankOrder($order) &&
            $this->orderHelper->isNew($order)
        );
    }

    /**
     * Check if configured environment is test.
     *
     * @param Order $order
     * @return bool
     */
    private function isTestEnvironment(
        Order $order
    ): bool {
        $env = $this->config->getEnvironment($order->getStore()->getCode());

        return $env === ResursBank::ENVIRONMENT_TEST;
    }
}
