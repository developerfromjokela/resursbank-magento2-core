<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

use Exception;
use Magento\Sales\Model\Order;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Order as OrderHelper;
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
    private $orderHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Log
     */
    private $log;

    /**
     * @param OrderHelper $orderHelper
     * @param Config $config
     * @param Log $log
     */
    public function __construct(
        OrderHelper $orderHelper,
        Config $config,
        Log $log
    ) {
        $this->orderHelper = $orderHelper;
        $this->config = $config;
        $this->log = $log;
    }

    /**
     * @param Order $subject
     * @param Order $result
     * @return Order
     */
    public function afterBeforeSave(
        Order $subject,
        Order $result
    ): Order {
        try {
            if ($this->orderHelper->isNew($subject)) {
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
