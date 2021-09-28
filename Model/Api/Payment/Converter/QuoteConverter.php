<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter;

use Exception;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory as TaxItemResourceFactory;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\Api\Payment\Converter\Item\DiscountItemFactory;
use Resursbank\Core\Model\Api\Payment\Converter\Item\Quote\ProductItemFactory;
use Resursbank\Core\Model\Api\Payment\Converter\Item\ShippingItemFactory;
use Resursbank\Core\Model\Api\Payment\Item as PaymentItem;
use function is_array;

/**
 * Quote entity conversion for payment payloads.
 */
class QuoteConverter extends AbstractConverter
{
    /**
     * @var ProductItemFactory
     */
    private ProductItemFactory $productItemFactory;

    /**
     * @param Log $log
     * @param TaxItemResourceFactory $taxResourceFactory
     * @param ShippingItemFactory $shippingItemFactory
     * @param DiscountItemFactory $discountItemFactory
     * @param ProductItemFactory $productItemFactory
     */
    public function __construct(
        Log $log,
        TaxItemResourceFactory $taxResourceFactory,
        ShippingItemFactory $shippingItemFactory,
        DiscountItemFactory $discountItemFactory,
        ProductItemFactory $productItemFactory
    ) {
        $this->productItemFactory = $productItemFactory;

        parent::__construct(
            $log,
            $taxResourceFactory,
            $shippingItemFactory,
            $discountItemFactory
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
    public function convert(
        Quote $entity
    ): array {
        $shippingAddress = $entity->getShippingAddress();
        $billingAddress = $entity->getBillingAddress();

        return array_merge(
            array_merge(
                $this->getShippingData(
                    (string) $shippingAddress->getShippingMethod(),
                    (string) $shippingAddress->getShippingDescription(),
                    (float) $shippingAddress->getShippingInclTax(),
                    $this->getShippingVatPct($shippingAddress)
                ),
                $this->getDiscountData(
                    (string) $entity->getCouponCode(),
                    $this->getDiscountAmount($shippingAddress, $billingAddress),
                    $this->getDiscountTax($shippingAddress, $billingAddress)
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
    protected function getProductData(
        Quote $entity
    ): array {
        $result = [];

        if ($this->includeProductData($entity)) {
            /** @var Item $product */
            foreach ($entity->getAllItems() as $product) {
                if ($product->getQty() > 0 &&
                    !$this->hasConfigurableParent($product)
                ) {
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
    public function includeProductData(
        Quote $entity
    ): bool {
        $items = $entity->getAllItems();

        return !empty($items);
    }

    /**
     * Retrieve VAT (tax percentage) of applied shipping method.
     *
     * @param Address $address
     * @return float
     */
    private function getShippingVatPct(
        Address $address
    ): float {
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
    public function getCollectedTotal(
        array $items
    ): float {
        $result = 0;

        foreach ($items as $item) {
            if ($item instanceof PaymentItem) {
                $result += (
                    $item->getUnitAmountWithoutVat() * $item->getQuantity()
                ) * (1 + $item->getVatPct() / 100);
            }
        }

        return (float) $result;
    }

    /**
     * Whether a product have a configurable product as a parent.
     *
     * @param Item $product
     * @return bool
     */
    private function hasConfigurableParent(
        Item $product
    ): bool {
        return (
            $product->getParentItem() instanceof Item &&
            $product->getParentItem()->getProductType() === 'configurable'
        );
    }

    /**
     * Unless you have a shopping cart consisting of only downloadable products
     * the discount data will be associated with your shipping address.
     * Otherwise it will be associated with your billing address instead, even
     * though there is a separate shipping address entity. This appears to be
     * a bug we cannot do much about at the time of writing.
     *
     * @param Address $shipping
     * @param Address $billing
     * @return float
     */
    private function getDiscountAmount(
        Address $shipping,
        Address $billing
    ): float {
        return (float) $shipping->getDiscountAmount() < 0.0 ?
            (float) $shipping->getDiscountAmount() :
            (float) $billing->getDiscountAmount();
    }

    /**
     * Please refer to the docblock of getDiscountAmount for an explanation.
     *
     * @param Address $shipping
     * @param Address $billing
     * @return float
     */
    private function getDiscountTax(
        Address $shipping,
        Address $billing
    ): float {
        return (float) $shipping->getDiscountTaxCompensationAmount() > 0.0 ?
            (float) $shipping->getDiscountTaxCompensationAmount() :
            (float) $billing->getDiscountTaxCompensationAmount();
    }
}
