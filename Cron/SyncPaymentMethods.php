<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Cron;

use Exception;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;

/**
 * Automatically sync payment methods for all configured accounts.
 */
class SyncPaymentMethods
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * @var PaymentMethods
     */
    private $paymentMethods;

    /**
     * @var Log
     */
    private $log;

    /**
     * @param Config $config
     * @param Credentials $credentials
     * @param PaymentMethods $paymentMethods
     * @param Log $log
     */
    public function __construct(
        Config $config,
        Credentials $credentials,
        PaymentMethods $paymentMethods,
        Log $log
    ) {
        $this->config = $config;
        $this->credentials = $credentials;
        $this->paymentMethods = $paymentMethods;
        $this->log = $log;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function execute(): void
    {
        if ($this->config->autoSyncPaymentMethods()) {
            try {
                foreach ($this->credentials->getCollection() as $credentials) {
                    $this->paymentMethods->sync($credentials);
                }
            } catch (Exception $e) {
                $this->log->exception($e);
            }
        }
    }
}
