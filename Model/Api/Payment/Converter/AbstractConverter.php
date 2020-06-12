<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter;

use Exception;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item as TaxItemResource;
use Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory as TaxItemResourceFactory;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\Api\Payment\Converter\Item\DiscountItem;
use Resursbank\Core\Model\Api\Payment\Converter\Item\DiscountItemFactory;
use Resursbank\Core\Model\Api\Payment\Converter\Item\PaymentFeeItem;
use Resursbank\Core\Model\Api\Payment\Converter\Item\PaymentFeeItemFactory;
use Resursbank\Core\Model\Api\Payment\Converter\Item\ShippingItem;
use Resursbank\Core\Model\Api\Payment\Converter\Item\ShippingItemFactory;
use Resursbank\Core\Model\Api\Payment\Item as PaymentItem;

/**
 * Basic data conversion class for payment payload.
 */
abstract class AbstractConverter implements ConverterInterface
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @var ShippingItemFactory
     */
    private $shippingItemFactory;

    /**
     * @var DiscountItemFactory
     */
    private $discountItemFactory;

    /**
     * @var TaxItemResourceFactory
     */
    private $taxResourceFactory;

    /**
     * @var PaymentFeeItemFactory
     */
    private $feeItemFactory;

    /**
     * @param Log $log
     * @param TaxItemResourceFactory $taxResourceFactory
     * @param ShippingItemFactory $shippingItemFactory
     * @param DiscountItemFactory $discountItemFactory
     * @param PaymentFeeItemFactory $feeItemFactory
     */
    public function __construct(
        Log $log,
        TaxItemResourceFactory $taxResourceFactory,
        ShippingItemFactory $shippingItemFactory,
        DiscountItemFactory $discountItemFactory,
        PaymentFeeItemFactory $feeItemFactory
    ) {
        $this->log = $log;
        $this->shippingItemFactory = $shippingItemFactory;
        $this->discountItemFactory = $discountItemFactory;
        $this->taxResourceFactory = $taxResourceFactory;
        $this->feeItemFactory = $feeItemFactory;
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
            /** @var ShippingItem $item */
            $item = $this->shippingItemFactory->create([
                'method' => $method,
                'description' => $description,
                'amount' => $amount,
                'vatPct' => $vatPct
            ]);

            $result[] = $item->getItem();
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getDiscountData(
        string $couponCode,
        float $amount,
        float $taxAmount
    ): array {
        $result = [];

        if ($this->includeDiscountData($amount)) {
            /** @var DiscountItem $item */
            $item = $this->discountItemFactory->create([
                'couponCode' => $couponCode,
                'amount' => $amount,
                'taxAmount' => $taxAmount
            ]);

            $result[] = $item->getItem();
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getPaymentFeeData(
        string $name,
        float $amount,
        float $vatPct
    ): array {
        $result = [];

        if ($this->includePaymentFeeData($amount)) {
            /** @var PaymentFeeItem $item */
            $item = $this->feeItemFactory->create([
                'name' => $name,
                'amount' => $amount,
                'vatPct' => $vatPct
            ]);

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
     * @inheritDoc
     */
    public function includeDiscountData(float $amount): bool
    {
        return ($amount < 0);
    }

    /**
     * @inheritDoc
     */
    public function includePaymentFeeData(float $amount): bool
    {
        return $amount > 0.0;
    }

    /**
     * Convert all PaymentItem instances to simple arrays the API can
     * understand.
     *
     * @param PaymentItem[] $items
     * @return array
     */
    public function convertItemsToArrays(array $items): array
    {
        $result = [];

        /** @var PaymentItem $item */
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

        /** @var TaxItemResource $taxItem */
        $taxItem = $this->taxResourceFactory->create();
        $collection = $taxItem->getTaxItemsByOrderId($orderId);

        $foundMatchingTaxItemType = false;

        /** @var array $item */
        foreach ($collection as $item) {
            if (is_array($item) &&
                isset($item['taxable_item_type']) &&
                $item['taxable_item_type'] === $type
            ) {
                $foundMatchingTaxItemType = true;

                $result = isset($item['tax_percent']) ?
                    (float) $item['tax_percent'] :
                    0.0;

                break;
            }
        }

        if (!$foundMatchingTaxItemType) {
            $this->log->info(
                'Could not find matching tax item type ' . $type . ' on ' .
                'order entity ' . $orderId
            );
        }

        return $result;
    }
}
