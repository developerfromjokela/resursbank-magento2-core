<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter\Item;

use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\Api\Payment\Item;
use Resursbank\Core\Model\Api\Payment\ItemFactory;

/**
 * Shipping data converter.
 */
class ShippingItem extends AbstractItem
{
    /**
     * @var string
     */
    private string $method;

    /**
     * @var string
     */
    private string $description;

    /**
     * @var float
     */
    private float $amount;

    /**
     * @var int
     */
    private int $vatPct;

    /**
     * @param Config $config
     * @param ItemFactory $itemFactory
     * @param Log $log
     * @param string $method Shipping method code.
     * @param string $description Shipping method title.
     * @param float $amount Amount incl. tax.
     * @param int $vatPct Tax percentage.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        ItemFactory $itemFactory,
        Log $log,
        string $method,
        string $description,
        float $amount,
        int $vatPct,
        StoreManagerInterface $storeManager
    ) {
        $this->method = $method;
        $this->description = $description;
        $this->amount = $amount;
        $this->vatPct = $vatPct;

        parent::__construct($config, $itemFactory, $log, $storeManager);
    }

    /**
     * @inheritDoc
     */
    public function getArtNo(): string
    {
        return $this->sanitizeArtNo($this->method);
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function getQuantity(): float
    {
        return 1.0;
    }

    /**
     * @inheritDoc
     */
    public function getUnitAmountWithoutVat(): float
    {
        $inclTax = $this->amount;
        $vatPct = $this->getVatPct();

        $result = ($inclTax > 0 && $vatPct > 0) ?
            $inclTax / (1 + ($vatPct / 100)) :
            $inclTax;

        return $this->sanitizeUnitAmountWithoutVat($result);
    }

    /**
     * @inheritDoc
     */
    public function getVatPct(): int
    {
        return $this->vatPct;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return Item::TYPE_SHIPPING;
    }

    /**
     * @inheritDoc
     */
    public function getTotalAmountInclVat(): float
    {
        return round(num: $this->amount, precision: 2);
    }
}
