<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Store\Model\StoreManager;

class Order extends AbstractHelper
{
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param StoreManager $storeManager
     */
    public function __construct(
        Context $context,
        StoreManager $storeManager
    ) {
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * Check if the supplied order is new.
     *
     * @param OrderModel $order
     * @return bool
     */
    public function isNew(
        OrderModel $order
    ): bool {
        return (
            $order->isObjectNew() &&
            !$order->getOriginalIncrementId() &&
            (float) $order->getGrandTotal() > 0
        );
    }

    /**
     * @param OrderInterface $order
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreCode(
        OrderInterface $order
    ): string {
        return $order instanceof OrderModel ?
            $order->getStore()->getCode() :
            $this->storeManager->getStore($order->getStoreId())->getCode();
    }
}
