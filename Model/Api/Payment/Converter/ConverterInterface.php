<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Converter;

use Resursbank\Core\Model\Api\Payment\Item as PaymentItem;

/**
 * Extract data from an entity (like an Order, Creditmemo etc.) and prepare
 * it to be used in an API call payload.
 *
 * There is no requirement for a getProductData or includeProductData method.
 * While typically used these methods take varied argument types, as such they
 * cannot be specified within this contract and are therefore considered
 * optional.
 */
interface ConverterInterface
{
    /**
     * Extract shipping information from $subject entity.
     *
     * @param string $method Shipping method code.
     * @param string $description Shipping method title.
     * @param float $amount Price incl. tax.
     * @param float $vatPct Tax percentage.
     * @return PaymentItem[]
     */
    public function getShippingData(
        string $method,
        string $description,
        float $amount,
        float $vatPct
    ): array;

    /**
     * Whether to include shipping data in payment payload.
     *
     * @param string $method
     * @param float $amount
     * @return bool
     */
    public function includeShippingData(
        string $method,
        float $amount
    ): bool;
}
