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
     * @var float
     */
    private float $amount;

    /**
     * @var int
     */
    private int $taxPercent;

    /**
     * @param Config $config
     * @param ItemFactory $itemFactory
     * @param Log $log
     * @param float $amount Amount incl. tax.
     * @param int $taxPercent Tax amount.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        ItemFactory $itemFactory,
        Log $log,
        float $amount,
        int $taxPercent,
        StoreManagerInterface $storeManager
    ) {
        $this->amount = $amount;
        $this->taxPercent = $taxPercent;

        parent::__construct($config, $itemFactory, $log, $storeManager);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getArtNo(): string
    {
        $result = 'discount_' . $this->getVatPct();
        $result .= time();

        return $this->sanitizeArtNo($result);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getDescription(): string
    {
        return 'Discount ' . $this->getVatPct() . '%';
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
        return $this->sanitizeUnitAmountWithoutVat($this->amount);
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
        return $this->taxPercent;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return Item::TYPE_DISCOUNT;
    }
}
