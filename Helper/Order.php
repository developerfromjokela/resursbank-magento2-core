<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order as OrderModel;

class Order extends AbstractHelper
{
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
}
