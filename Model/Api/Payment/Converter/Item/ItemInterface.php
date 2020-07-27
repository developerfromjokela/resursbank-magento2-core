<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter\Item;

use Resursbank\Core\Model\Api\Payment\Item;

/**
 * Extract data from an entity (like an Order Item or Creditmemo Item) and
 * prepare it to be used in an API payment payload.
 *
 * NOTE: excl. tax prices are re-calculated using the incl. tax price and tax
 * percentage to provide Resurs Bank with accurate values. Magento rounds the
 * excl. / incl. tax prices. Depending on the utilised API flow, we only provide
 * Resurs Bank with the excl. tax price (unitAmountWithoutVat) and tax
 * percentage (vatPct) values. Resurs Bank then calculate the incl. tax price
 * using these values. This means we cannot submit rounded values to Resurs Bank
 * since this can incur a slight price difference (depending on how you've
 * configured tax settings and prices in Magento).
 */
interface ItemInterface
{
    /**
     * @return Item
     */
    public function getItem(): Item;

    /**
     * @return string
     */
    public function getArtNo(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return float
     */
    public function getQuantity(): float;

    /**
     * @return string
     */
    public function getUnitMeasure(): string;

    /**
     * @return float
     */
    public function getUnitAmountWithoutVat(): float;

    /**
     * @return int
     */
    public function getVatPct(): int;

    /**
     * @return string
     */
    public function getType(): string;
}
