<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Cron;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Api as ApiHelper;
use Resursbank\Core\Helper\Order as OrderHelper;
use Resursbank\Core\Helper\PaymentMethods as PaymentHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;
use ResursException;
use Throwable;
use TorneLIB\Exception\ExceptionHandler;
use function is_string;

/**
 * Cleans up stale order with the state pending_payment.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CleanOrders
{
    private OrderHelper $orderHelper;
    private PaymentHelper $paymentHelper;
    private ApiHelper $apiHelper;
    private CollectionFactory $orderCollectionFactory;
    private StoreManagerInterface $storeManager;
    private Config $config;
    private Log $log;

    /**
     * @param Log $log
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $orderCollectionFactory
     * @param OrderHelper $orderHelper
     * @param PaymentHelper $paymentHelper
     * @param ApiHelper $apiHelper
     */
    public function __construct(
        Log $log,
        Config $config,
        StoreManagerInterface $storeManager,
        CollectionFactory $orderCollectionFactory,
        OrderHelper $orderHelper,
        PaymentHelper $paymentHelper,
        ApiHelper $apiHelper
    ) {
        $this->log = $log;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->apiHelper = $apiHelper;
        $this->paymentHelper = $paymentHelper;
        $this->orderHelper = $orderHelper;
    }

    /**
     * Performs the actual cleanup.
     *
     * @return void
     */
    public function execute(): void
    {
        $this->log->info('Clean orders cron job running!');

        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            if ($this->config->isCleanOrdersEnabled($store->getCode())) {
                $this->log->info(
                    'Looking for stale pending orders on store ' .
                    $store->getName()
                );

                $minimumAge = $this->config->getCleanOrdersMinimumAge(
                    $store->getCode()
                );

                $orders = $this->orderCollectionFactory->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter(
                        'status',
                        Order::STATE_PENDING_PAYMENT
                    )
                    ->addFieldToFilter(
                        'store_id',
                        ['eq' => $store->getId()]
                    )
                    ->addFieldToFilter(
                        'updated_at',
                        ['to' => date(
                            'Y-m-d H:i:s',
                            time()-$minimumAge
                        )]
                    )
                    ->load();

                if (count($orders) === 0) {
                    continue;
                }

                $this->log->info('Found ' . count($orders) .
                    ' stale pending orders. Attempting to cancel...');

                /** @var Order $order */
                foreach ($orders as $order) {
                    try {
                        if ($this->paymentHelper->isResursBankOrder(
                            $order
                        ) &&
                            $this->isInactive($order)
                        ) {
                            $this->orderHelper->cancelOrder($order);
                            $this->log->info(
                                'Successfully canceled stale pending order ' .
                                $order->getIncrementId() . '.'
                            );
                        }
                    } catch (Throwable $error) {
                        $this->log->error(
                            'Automated cancel of stale pending order ' .
                            $order->getIncrementId() . ' failed.'
                        );
                        $this->log->exception($error);
                    }
                }
            }
        }

        $this->log->info('Clean orders cron job run finished.');
    }

    /**
     * Check if session is inactive/not created at Resurs.
     *
     * @param Order $order
     * @return bool
     * @throws LocalizedException
     * @throws ValidatorException
     * @throws ResursException
     * @throws InvalidDataException
     * @throws ExceptionHandler
     */
    public function isInactive(Order $order): bool
    {
        return $this->apiHelper->getPayment($order) === null;
    }
}
