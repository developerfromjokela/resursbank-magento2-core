<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter;

use Exception;
use function is_array;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory as TaxItemResourceFactory;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\Api\Payment\Converter\Item\DiscountItemFactory;
use Resursbank\Core\Model\Api\Payment\Converter\Item\PaymentFeeItemFactory;
use Resursbank\Core\Model\Api\Payment\Converter\Item\Quote\ProductItem;
use Resursbank\Core\Model\Api\Payment\Converter\Item\Quote\ProductItemFactory;
use Resursbank\Core\Model\Api\Payment\Converter\Item\ShippingItemFactory;
use Resursbank\Core\Model\Api\Payment\Item as PaymentItem;

/**
 * Quote entity conversion for payment payloads.
 */
class QuoteConverter extends AbstractConverter
{
    /**
     * @var ProductItemFactory
     */
    private $productItemFactory;

    /**
     * @param Log $log
     * @param TaxItemResourceFactory $taxResourceFactory
     * @param ShippingItemFactory $shippingItemFactory
     * @param DiscountItemFactory $discountItemFactory
     * @param ProductItemFactory $productItemFactory
     * @param PaymentFeeItemFactory $feeItemFactory
     */
    public function __construct(
        Log $log,
        TaxItemResourceFactory $taxResourceFactory,
        ShippingItemFactory $shippingItemFactory,
        DiscountItemFactory $discountItemFactory,
        ProductItemFactory $productItemFactory,
        PaymentFeeItemFactory $feeItemFactory
    ) {
        $this->productItemFactory = $productItemFactory;

        parent::__construct(
            $log,
            $taxResourceFactory,
            $shippingItemFactory,
            $discountItemFactory,
            $feeItemFactory
        );
    }

    /**
     * Convert supplied entity to a collection of PaymentItem instances. These
     * objects can later be mutated into a simple array the API can interpret.
     *
     * @param Quote $entity
     * @return PaymentItem[]
     * @throws Exception
     */
    public function convert(Quote $entity): array
    {
        /** @var Address $address */
        $address = $entity->getShippingAddress();

        return array_merge(
            array_merge(
                $this->getShippingData(
                    (string) $address->getShippingMethod(),
                    (string) $address->getShippingDescription(),
                    (float) $address->getShippingInclTax(),
                    $this->getShippingVatPct($address)
                ),
                $this->getDiscountData(
                    (string) $entity->getCouponCode(),
                    (float) $address->getDiscountAmount(),
                    (float) $address->getDiscountTaxCompensationAmount()
                )
            ),
            $this->getProductData($entity)
        );
    }

    /**
     * Extract product information from Quote entity.
     *
     * @param Quote $entity
     * @return PaymentItem[]
     * @throws Exception
     */
    protected function getProductData(Quote $entity): array
    {
        $result = [];

        if ($this->includeProductData($entity)) {
            /** @var Item $product */
            foreach ($entity->getAllItems() as $product) {
                if ($product->getQty() > 0) {
                    /** @var ProductItem $item */
                    $item = $this->productItemFactory->create([
                        'product' => $product
                    ]);

                    $result[] = $item->getItem();
                }
            }
        }

        return $result;
    }

    /**
     * Whether or not to include product data in payment payload.
     *
     * @param Quote $entity
     * @return bool
     */
    public function includeProductData(Quote $entity): bool
    {
        $items = $entity->getAllItems();

        return !empty($items);
    }

    /**
     * Retrieve VAT (tax percentage) of applied shipping method.
     *
     * @param Address $address
     * @return float
     */
    private function getShippingVatPct(Address $address): float
    {
        $result = 0.0;

        $taxes = $address->getData('items_applied_taxes');

        if (is_array($taxes) && isset($taxes['shipping'][0]['percent'])) {
            $result = (float) $taxes['shipping'][0]['percent'];
        }

        return $result;
    }

    /**
     * Assemble total value of converted quote. This lets us check the actual
     * data we are submitting to Resurs Bank.
     *
     * @param PaymentItem[] $items
     * @return float
     */
    public function getCollectedTotal(array $items): float
    {
        $result = 0;

        /** @var PaymentItem $item */
        foreach ($items as $item) {
            if ($item instanceof PaymentItem) {
                $result += (
                    $item->getUnitAmountWithoutVat() * $item->getQuantity()
                ) * (1 + $item->getVatPct() / 100);
            }
        }

        return (float) $result;
    }
}
