<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter\Item;

use Exception;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\Api\Payment\Item;
use Resursbank\Core\Model\Api\Payment\Item\Validation\ArtNo;
use Resursbank\Core\Model\Api\Payment\Item\Validation\UnitAmountWithoutVat;
use Resursbank\Core\Model\Api\Payment\ItemFactory;

use function strlen;

/**
 * Convert an item entity, such as an Order Item, into an object prepared for a
 * payment payload.
 */
abstract class AbstractItem implements ItemInterface
{
    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var ItemFactory
     */
    private ItemFactory $itemFactory;

    /**
     * @var Log
     */
    protected Log $log;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Config $config
     * @param ItemFactory $itemFactory
     * @param Log $log
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        ItemFactory $itemFactory,
        Log $log,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->itemFactory = $itemFactory;
        $this->log = $log;
        $this->storeManager = $storeManager;
    }

    /**
     * @return Item
     * @throws Exception
     */
    public function getItem(): Item
    {
        return $this->itemFactory->create(data: [
            Item::KEY_ART_NO => $this->getArtNo(),
            Item::KEY_DESCRIPTION => $this->getDescription(),
            Item::KEY_QUANTITY => $this->getQuantity(),
            Item::KEY_UNIT_MEASURE => $this->getUnitMeasure(),
            Item::KEY_UNIT_AMOUNT_WITHOUT_VAT => $this->getUnitAmountWithoutVat(),
            Item::KEY_VAT_PCT => $this->getVatPct(),
            Item::KEY_TYPE => $this->getType(),
            Item::KEY_TOTAL_AMOUNT_INCL_VAT => $this->getTotalAmountInclVat()
        ]);
    }

    /**
     * Unit measurement configuration value.
     *
     * @return string
     */
    public function getUnitMeasure(): string
    {
        return Item::UNIT_MEASURE;
    }

    /**
     * Whether to round tax percentage values.
     *
     * NOTE: This currently only applies to payment lines with type DISCOUNT,
     * or ORDER_LINE lines including payment fee information.
     *
     * @return bool
     * @throws Exception
     */
    public function roundTaxPercentage(): bool
    {
        $result = false;

        try {
            $result = $this->config->roundTaxPercentage(
                $this->storeManager->getStore()->getCode()
            );
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Removes all illegal characters for the "artNo" property. String length
     * may not exceed 100 characters. Please refer to the linked documentation
     * for further information.
     *
     * @param string $artNo
     * @return string
     * @link https://test.resurs.com/docs/display/ecom/Hosted+payment+flow+data
     */
    public function sanitizeArtNo(
        string $artNo
    ): string {
        $result = (string) preg_replace(ArtNo::REGEX, '', strtolower($artNo));

        if (strlen($result) > ArtNo::MAX_LENGTH) {
            $result = substr($result, 0, ArtNo::MAX_LENGTH);
        }

        return $result;
    }

    /**
     * The "unitAmountWithoutVat" property may not include more than 5 decimals.
     * Please refer to the linked documentation for further information.
     *
     * @param float $amount
     * @return float
     * @link https://test.resurs.com/docs/display/ecom/Hosted+payment+flow+data
     */
    public function sanitizeUnitAmountWithoutVat(
        float $amount
    ): float {
        return round($amount, UnitAmountWithoutVat::MAX_DECIMAL_LENGTH);
    }

    /**
     * @param CreditmemoItem|OrderItem|QuoteItem|AbstractModel $item
     * @return mixed
     */
    public function getOrderId(
        AbstractModel $item
    ) {
        return ($item instanceof OrderItem) ?
            $item->getId() :
            $item->getOrderId(); /** @phpstan-ignore-line */
    }
}
