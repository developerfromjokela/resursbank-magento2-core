<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter\Item;

use Exception;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\Api\Payment\Item;
use Resursbank\Core\Model\Api\Payment\ItemFactory;

/**
 * Discount data converter.
 */
class DiscountItem extends AbstractItem
{
    /**
     * @var string
     */
    private string $couponCode;

    /**
     * @var float
     */
    private float $amount;

    /**
     * @var float
     */
    private float $taxAmount;

    /**
     * @param Config $config
     * @param ItemFactory $itemFactory
     * @param Log $log
     * @param string $couponCode
     * @param float $amount Amount incl. tax.
     * @param float $taxAmount Tax amount.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        ItemFactory $itemFactory,
        Log $log,
        string $couponCode,
        float $amount,
        float $taxAmount,
        StoreManagerInterface $storeManager
    ) {
        $this->couponCode = $couponCode;
        $this->amount = $amount;
        $this->taxAmount = $taxAmount;

        parent::__construct($config, $itemFactory, $log, $storeManager);
    }

    /**
     * @inheritDoc
     */
    public function getArtNo(): string
    {
        $result = 'discount';

        if ($this->couponCode !== '') {
            $result .= $this->couponCode;
        }

        $result .= time();

        return $this->sanitizeArtNo($result);
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        $result = 'Discount';

        if ($this->couponCode !== '') {
            $result .= " ({$this->couponCode})";
        }

        return $result;
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
     * @throws Exception
     */
    public function getUnitAmountWithoutVat(): float
    {
        return $this->sanitizeUnitAmountWithoutVat(
            $this->amount + $this->taxAmount
        );

//        $vatPct = $this->getVatPct();
//
//        $result = ($this->amount < 0 && $vatPct > 0) ?
//            ($this->amount / (1 + ($vatPct / 100))) :
//            $this->amount;
//
//        return $this->sanitizeUnitAmountWithoutVat($result);
    }

    /**
     * NOTE: the tax percentage value is being rounded here because it should
     * always be an integer. Shipping and product tax percentage are stored with
     * the order entity, so we can re-calculate their accurate excl. tax
     * price (for more on this, please refer to the docblock of
     * ConverterItemInterface). Since we cannot safely obtain the applied tax
     * percentage value for discounts we will need to calculate it using the
     * excl. / incl. tax values of the discount. However, this will leave us
     * with a value like '24.966934532%' since Magento rounds the excl. / incl.
     * tax discount prices. So, we round of the tax percentage value, and we
     * will later use our correct tax percentage to re-calculate the accurate
     * excl. tax price. This ensures the prices will be the same both in Magento
     * and at Resurs Bank.
     *
     * @inheritDoc
     * @throws Exception
     */
    public function getVatPct(): int
    {
        return 0;
//        $exclTax = abs($this->amount) - $this->taxAmount;
//
//        $result = ($exclTax > 0 && $this->taxAmount > 0) ?
//            (($this->taxAmount / $exclTax) * 100) :
//            0.0;
//
//        // VAT percentage should always be an int, unless explicitly configured.
//        if ($this->roundTaxPercentage()) {
//            $result = round($result);
//        }
//
//        return (int) $result;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return Item::TYPE_DISCOUNT;
    }
}
