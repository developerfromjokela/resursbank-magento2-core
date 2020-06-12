<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter\Item;

use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\Api\Payment\Item;
use Resursbank\Core\Model\Api\Payment\ItemFactory;

/**
 * Shipping data converter.
 */
class ShippingItem extends AbstractItem implements ItemInterface
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $description;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var float
     */
    private $vatPct;

    /**
     * @param ApiConfig $apiConfig
     * @param AdvancedConfig $advancedConfig
     * @param ItemFactory $itemFactory
     * @param Log $log
     * @param string $method Shipping method code.
     * @param string $description Shipping method title.
     * @param float $amount Amount incl. tax.
     * @param float $vatPct Tax percentage.
     */
    public function __construct(
        ApiConfig $apiConfig,
        AdvancedConfig $advancedConfig,
        ItemFactory $itemFactory,
        Log $log,
        string $method,
        string $description,
        float $amount,
        float $vatPct
    ) {
        $this->method = $method;
        $this->description = $description;
        $this->amount = $amount;
        $this->vatPct = $vatPct;

        parent::__construct(
            $apiConfig,
            $advancedConfig,
            $itemFactory,
            $log
        );
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
    public function getVatPct(): float
    {
        return $this->sanitizeVatPct(
            $this->vatPct
        );
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return Item::TYPE_SHIPPING;
    }
}
