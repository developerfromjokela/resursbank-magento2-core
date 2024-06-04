<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Data\Order;

use Magento\Payment\Gateway\Data\Order\AddressAdapterFactory;
use Magento\Sales\Model\Order;
use Magento\Store\Model\Store;

/**
 * This class primarily exists as a "pure" alternative to the Braintree
 * type keeps popping up unless we actively set a preference for another type.
 */
class OrderAdapter extends \Magento\Payment\Gateway\Data\Order\OrderAdapter
{
    /** @var Order */
    private readonly Order $_order;

    /**
     * @param Order $order
     * @param AddressAdapterFactory $addressAdapterFactory
     */
    public function __construct(
        Order $order,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        $this->_order = $order;
        parent::__construct($order, $addressAdapterFactory);
    }

    /**
     * Get order store.
     *
     * @return Store
     */
    public function getStore(): Store
    {
        return $this->_order->getStore();
    }

    /**
     * Get order.
     *
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->_order;
    }
}
