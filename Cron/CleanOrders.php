<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Cron;

use JsonException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use ReflectionException;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Api as ApiHelper;
use Resursbank\Core\Helper\Order as OrderHelper;
use Resursbank\Core\Helper\PaymentMethods as PaymentHelper;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Module\PaymentHistory\Repository
    as PaymentHistoryRepository;
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
        private readonly Log $log,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly CollectionFactory $orderCollectionFactory,
        private readonly OrderHelper $orderHelper,
        private readonly PaymentHelper $paymentHelper,
        private readonly ApiHelper $apiHelper
    ) {
    }

    /**
     * Performs the actual cleanup.
     *
     * @return void
     */
    public function execute(): void
    {
        $this->log->info(text: 'Clean orders cron job running!');

        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            if ($this->config->isCleanOrdersActive(scopeCode: $store->getCode())) {
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
                        field: 'status',
                        condition: Order::STATE_PENDING_PAYMENT
                    )
                    ->addFieldToFilter(
                        field: 'store_id',
                        condition: ['eq' => $store->getId()]
                    )
                    ->addFieldToFilter(
                        field: 'updated_at',
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
                        if ($this->paymentHelper->isResursBankOrder(
                            order: $order
                        ) &&
                            $this->isInactive(order: $order)
                        ) {
                            $this->orderHelper->cancelOrder(order: $order);
                            PaymentHistoryRepository::write(
                                entry: $this->getEntry(order: $order)
                            );
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

    /**
     * Generate Entry object for payment history.
     *
     * This is a separate method so that it can be intercepted.
     *
     * @param Order $order
     * @return Entry
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws JsonException
     */
    public function getEntry(
        Order $order
    ): Entry {
        return new Entry(
            paymentId: $order->getIncrementId(),
            event: Event::ORDER_CANCELED_CRON,
            user: User::CRON,
            extra: 'Job code: resursbank_core_clean_orders'
        );
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
        return $this->apiHelper->getPayment(order: $order) === null;
    }
}
