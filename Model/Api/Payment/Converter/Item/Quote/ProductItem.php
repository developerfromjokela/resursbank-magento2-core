<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter\Item\Quote;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Model\Api\Payment\Item;
use Resursbank\Core\Model\Api\Payment\Converter\Item\AbstractItem;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Model\Api\Payment\ItemFactory;
use Resursbank\Core\Helper\Log;

/**
 * Product data converter.
 */
class ProductItem extends AbstractItem
{
    /**
     * @var QuoteItem
     */
    protected QuoteItem $product;

    /**
     * @param Config $config
     * @param ItemFactory $itemFactory
     * @param Log $log
     * @param QuoteItem $product
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        ItemFactory $itemFactory,
        Log $log,
        QuoteItem $product,
        StoreManagerInterface $storeManager
    ) {
        $this->product = $product;

        parent::__construct($config, $itemFactory, $log, $storeManager);
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
        return $this->isBundle() ?
            (float) $this->product->getQty() :
            0.0;
    }

    /**
     * Retrieves the price without tax from a product, and returns it. The type
     * of the product, whether it has a parent or children, and how the price
     * is calculated determines the returned value. The point is that a payment
     * entry should mirror the order in Magento as closely as possible, which
     * is what the following examples will try to illustrate.
     *
     * A: If the product is a bundle, and the price calculation is set to
     * fixed, then the price of the bundle is determined by the fixed price of
     * the bundle plus the overriding prices of the children as dictated by
     * the bundle. 0.0 will be returned for every child product.
     *
     * B: If the product is a bundle, and the price calculation is set to
     * dynamic, then the price of the bundle is determined by the original
     * prices of its children, and the bundle will have a price of 0.0
     * returned. The children on the other hand will have their original prices
     * returned which will make up the entire cost of the bundle.
     *
     * C: If the product is a configurable, its children will have 0.0
     * returned, while the configurable parent will have the entire price
     * returned.
     *
     * @inheritDoc
     */
    public function getUnitAmountWithoutVat(): float
    {
        return $this->isBundle() && !$this->hasFixedPrice() ?
            0.0 :
            (float)$this->product->getConvertedPrice();
    }

    /**
     * Retrieve the VAT percentage of a product. The type of the product,
     * whether it has a parent or children, and how the price is calculated
     * determines the returned value. The point is that a payment entry should
     * mirror the order in Magento as closely as possible, which is what the
     * following examples will try to illustrate.
     *
     * A: Children of a bundled product with fixed price calculation does not
     * have VAT percentage values. The value is supplied directly by the bundle.
     *
     * B: Children of a configurable product does not have VAT percentage
     * values. The value is supplied directly by the configurable product.
     *
     * C: All other product types have their own VAT percentages values.
     *
     * @inheritDoc
     */
    public function getVatPct(): int
    {
        if ($this->isBundle()) {
            $result = $this->hasFixedPrice() ?
                (float)(
                    $this->product->getTaxAmount() /
                    $this->product->getConvertedPrice()
                ) * 100 :
                0.0;
        } else {
            $result = (float) $this->product->getTaxPercent();
        }

        return (int)round($result);
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return Item::TYPE_PRODUCT;
    }

    /**
     * Checks if the product has fixed pricing by its parent's product options.
     * If a parent can't be found the product itself will be checked.
     *
     * @return bool
     */
    public function hasFixedPrice(): bool
    {
        return !$this->product->isChildrenCalculated();
    }

    /**
     * @return bool
     */
    public function isBundle(): bool
    {
        return $this->product->getProductType() === 'bundle';
    }
}
