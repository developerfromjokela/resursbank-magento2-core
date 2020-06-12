<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Item\Validation;

use Resursbank\Core\Model\Api\Payment\Item;

/**
 * Validation routines for property "type".
 */
class Type extends AbstractValidation implements ValidationInterface
{
    /**
     * @var string[]
     */
    private const ALLOWED_VALUES = [
        Item::TYPE_PRODUCT,
        Item::TYPE_DISCOUNT,
        Item::TYPE_SHIPPING
    ];

    /**
     * @inheritDoc
     */
    public function validate(
        string $value = ''
    ): void {
        $this->isOneOf($value, self::ALLOWED_VALUES);
    }
}
