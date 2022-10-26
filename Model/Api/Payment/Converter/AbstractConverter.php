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
    private Log $log;

    /**
     * @var ShippingItemFactory
     */
    private ShippingItemFactory $shippingItemFactory;

    /**
     * @var DiscountItemFactory
     */
    private DiscountItemFactory $discountItemFactory;

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
     */
    public function includeShippingData(
        string $method,
        float $amount
    ): bool {
        return ($method !== '' && $amount > 0);
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
