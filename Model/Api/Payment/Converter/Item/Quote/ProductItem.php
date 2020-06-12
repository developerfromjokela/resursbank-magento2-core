<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter\Item\Quote;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Resursbank\Core\Model\Api\Payment\Converter\Item\ItemInterface;
use Resursbank\Core\Model\Api\Payment\Item;
use Resursbank\Core\Model\Api\Payment\Converter\Item\AbstractItem;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Model\Api\Payment\ItemFactory;
use Resursbank\Core\Helper\Log;

/**
 * Product data converter.
 */
class ProductItem extends AbstractItem implements ItemInterface
{
    /**
     * @var QuoteItem
     */
    protected $product;

    /**
     * @param ApiConfig $apiConfig
     * @param AdvancedConfig $advancedConfig
     * @param ItemFactory $itemFactory
     * @param Log $log
     * @param QuoteItem $product
     */
    public function __construct(
        ApiConfig $apiConfig,
        AdvancedConfig $advancedConfig,
        ItemFactory $itemFactory,
        Log $log,
        QuoteItem $product
    ) {
        $this->product = $product;

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
        return $this->sanitizeArtNo((string) $this->product->getSku());
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return (string) $this->product->getName();
    }

    /**
     * @inheritDoc
     */
    public function getQuantity(): float
    {
        return (float) $this->product->getQty();
    }

    /**
     * @inheritDoc
     */
    public function getUnitAmountWithoutVat(): float
    {
        return $this->sanitizeUnitAmountWithoutVat(
            (float) $this->product->getConvertedPrice()
        );
    }

    /**
     * @inheritDoc
     */
    public function getVatPct(): float
    {
        return $this->sanitizeVatPct(
            (float) $this->product->getTaxPercent()
        );
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return Item::TYPE_PRODUCT;
    }
}
