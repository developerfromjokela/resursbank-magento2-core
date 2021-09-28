<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Cron;

use Exception;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;

/**
 * Automatically sync Resurs Bank data for all configured accounts.
 */
class SyncData
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var Credentials
     */
    private Credentials $credentials;

    /**
     * @var PaymentMethods
     */
    private PaymentMethods $paymentMethods;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Config $config
     * @param Credentials $credentials
     * @param PaymentMethods $paymentMethods
     * @param Log $log
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        Credentials $credentials,
        PaymentMethods $paymentMethods,
        Log $log,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->credentials = $credentials;
        $this->paymentMethods = $paymentMethods;
        $this->log = $log;
        $this->storeManager = $storeManager;
    }

    /**
     * NOTE: We deactivate all methods currently in our table before we sync
     * from the API. The methods need to be retained locally, to ensure the
     * functionality of past orders utilising expired methods.
     *
     * @return void
     * @throws Exception
     */
    public function execute(): void
    {
        $storeCode = $this->storeManager->getStore()->getCode();

        if ($this->config->autoSyncData($storeCode)) {
            try {
                $this->paymentMethods->deactivateMethods();

                foreach ($this->credentials->getCollection() as $credentials) {
                    $this->paymentMethods->sync($credentials);
                }
            } catch (Exception $e) {
                $this->log->exception($e);
            }
        }
    }
}
