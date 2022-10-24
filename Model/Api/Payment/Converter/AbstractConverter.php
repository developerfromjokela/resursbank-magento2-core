<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter;

use Exception;
use Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory as TaxItemResourceFactory;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\Api\Payment\Converter\Item\DiscountItem;
use Resursbank\Core\Model\Api\Payment\Converter\Item\DiscountItemFactory;
use Resursbank\Core\Model\Api\Payment\Converter\Item\ShippingItemFactory;
use Resursbank\Core\Model\Api\Payment\Item as PaymentItem;
use function is_array;

/**
 * Basic data conversion class for payment payload.
 */
abstract class AbstractConverter implements ConverterInterface
{
    /**
     * @var Log
     */
    protected Log $log;

    /**
     * @var ShippingItemFactory
     */
    private ShippingItemFactory $shippingItemFactory;

    /**
     * @var DiscountItemFactory
     */
    public DiscountItemFactory $discountItemFactory;

    /**
     * @var TaxItemResourceFactory
     */
    private TaxItemResourceFactory $taxResourceFactory;

    /**
     * @param Log $log
     * @param TaxItemResourceFactory $taxResourceFactory
     * @param ShippingItemFactory $shippingItemFactory
     * @param DiscountItemFactory $discountItemFactory
     */
    public function __construct(
        Log $log,
        TaxItemResourceFactory $taxResourceFactory,
        ShippingItemFactory $shippingItemFactory,
        DiscountItemFactory $discountItemFactory
    ) {
        $this->log = $log;
        $this->shippingItemFactory = $shippingItemFactory;
        $this->discountItemFactory = $discountItemFactory;
        $this->taxResourceFactory = $taxResourceFactory;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getShippingData(
        string $method,
        string $description,
        float $amount,
        float $vatPct
    ): array {
        $result = [];

        if ($this->includeShippingData($method, $amount)) {
            $item = $this->shippingItemFactory->create(compact([
                'method',
                'description',
                'amount',
                'vatPct'
            ]));

            $result[] = $item->getItem();
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getDiscountItem(
        float $amount,
        float $taxAmount
    ): array {
        $result = [];

        if ($this->includeDiscountData($amount)) {
            $item = $this->discountItemFactory->create(compact([
                'amount',
                'taxAmount'
            ]));

            $result[] = $item->getItem();
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getDiscountData(
        float $amount,
        float $taxAmount
    ): array {
        $result = [];

        if ($this->includeDiscountData($amount)) {
            $item = $this->discountItemFactory->create(compact([
                'amount',
                'taxAmount'
            ]));

            $result[] = $item->getItem();
        }

        return $result;
    }

    /**
     * Resolve array of discount items made unique by their VAT percentage.
     *
     * @param array $items
     * @return array
     * @throws Exception
     */
    public function mergeDiscountItems(
        array $items
    ): array {
        $result = [];

        foreach ($items as $item) {
            $this->log->info('Merging items......');
            if ($item instanceof DiscountItem) {
                $this->log->info('Found one....');
                $vat = $item->getVatPct();

                if (isset($result[$vat])) {
                    $this->log->info('Same item found....');
                    $result[$vat]->addAmount(
                        $item->getUnitAmountWithoutVat() * $item->getQuantity()
                    );
                } else {
                    $this->log->info('New item found....');
                    $result[$vat] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function includeShippingData(
        string $method,
        float $amount
    ): bool {
        return ($method !== '' && $amount > 0);
    }

    /**
     * @inheritDoc
     */
    public function includeDiscountData(
        float $amount
    ): bool {
        return ($amount < 0);
    }

    /**
     * Convert all PaymentItem instances to simple arrays the API can
     * understand.
     *
     * @param PaymentItem[] $items
     * @return array<array>
     */
    public function convertItemsToArrays(
        array $items
    ): array {
        $result = [];

        foreach ($items as $item) {
            $result[] = $item->toArray();
        }

        return $result;
    }

    /**
     * Retrieve applied tax percentage from order entity by type (product,
     * shipping etc.).
     *
     * @param int $orderId
     * @param string $type
     * @return float
     */
    public function getTaxPercentage(
        int $orderId,
        string $type
    ): float {
        $result = 0.0;

        $taxItem = $this->taxResourceFactory->create();
        $collection = $taxItem->getTaxItemsByOrderId($orderId);

        $match = false;

        /** @var array<mixed> $item */
        foreach ($collection as $item) {
            if (is_array($item) &&
                isset($item['taxable_item_type']) &&
                $item['taxable_item_type'] === $type
            ) {
                $match = true;

                $result = isset($item['tax_percent']) ?
                    (float) $item['tax_percent'] :
                    0.0;

                break;
            }
        }

        if (!$match) {
            $this->log->info(
                'Could not find matching tax item type ' . $type . ' on ' .
                'order entity ' . $orderId
            );
        }

        return $result;
    }

    /**
     * Append discount item to passed array. We pass an array this way to
     * combine discount items, resulting in one item for each VAT percentage.
     *
     * @param float $amount
     * @param int $taxPercent
     * @param float $productQty
     * @param array $items
     * @return void
     */
    public function addDiscountItem(
        float $amount,
        int $taxPercent,
        float $productQty,
        array &$items
    ): void {
        if ($amount > 0) {
            if ($taxPercent > 0) {
                $amount /= (1 + ($taxPercent / 100));
            }

            // When applying a complex payment context, such as a percentage
            // based discount in combination with percentage based shipping
            // prices or certain tax settings, the discount amount can be
            // subject to a rounding error of 0.01. We can safely mitigate this
            // if a customer purchase specifically one product by removing the
            // fractions after the first two decimals.
            if ($productQty === 1.0) {
                $amount = (float) number_format($amount, 2, '.', '');
            }

            $item = $this->discountItemFactory->create(
                [
                    'amount' => 0 - $amount,
                    'taxPercent' => $taxPercent
                ]
            );

            $discountItem = $item->getItem();
            $found = false;

            foreach ($items as $existingItem) {
                if ($existingItem->getVatPct() === $discountItem->getVatPct()) {
                    $existingItem->setUnitAmountWithoutVat(
                        $existingItem->getUnitAmountWithoutVat() +
                        $discountItem->getUnitAmountWithoutVat()
                    );
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $items[] = $discountItem;
            }
        }
    }
}
