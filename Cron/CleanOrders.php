<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Cron;

use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Order as OrderHelper;
use Resursbank\Core\Helper\PaymentMethods as PaymentHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;
use Throwable;
use function is_string;

/**
 * Cleans up stale order with the state pending_payment.
 */
class CleanOrders
{
    /**
     * @param Log $log
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $orderCollectionFactory
     * @param OrderHelper $orderHelper
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        private readonly Log $log,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly CollectionFactory $orderCollectionFactory,
        private readonly OrderHelper $orderHelper,
        private readonly PaymentHelper $paymentHelper
    ) {
    }

    /**
     * Performs the actual cleanup.
     *
     * @return void
     */
    public function execute(): void
    {
        $this->log->error(text: 'Clean orders cron job running!');

        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            if ($this->config->getCleanOrders(scopeCode: $store->getCode())) {
                $this->log->info(
                    text: 'Looking for stale pending orders on store ' .
                    $store->getName()
                );

                $minimumAge = $this->config->getCleanOrdersMinimumAge(
                    scopeCode: $store->getCode()
                );

                $orders = $this->orderCollectionFactory->create()
                    ->addFieldToSelect(field: '*')
                    ->addFieldToFilter(
                        field: 'state',
                        condition: Order::STATE_PENDING_PAYMENT
                    )
                    ->addFieldToFilter(
                        field: 'created_at',
                        condition: ['to' => date(
                            format: 'Y-m-d H:i:s',
                            timestamp: time()-$minimumAge
                        )]
                    )
                    ->load();

                if (count($orders) === 0) {
                    continue;
                }

                $this->log->info(text: 'Found ' . count($orders) .
                    ' stale pending orders. Attempting to cancel...');

                /** @var Order $order */
                foreach ($orders as $order) {
                    try {
                        if ($this->paymentHelper->isResursBankOrder(order: $order)) {
                            $this->orderHelper->cancelOrder(order: $order);
                            $this->log->info(
                                text: 'Successfully canceled stale pending order ' .
                                $order->getIncrementId() . '.'
                            );
                        }
                    } catch (Throwable $error) {
                        $this->log->error(
                            text: 'Automated cancel of stale pending order ' .
                            $order->getIncrementId() . ' failed.'
                        );
                        $this->log->exception(error: $error);
                    }
                }
            }
        }

        $this->log->info(text: 'Clean orders cron job run finished.');
    }
}
