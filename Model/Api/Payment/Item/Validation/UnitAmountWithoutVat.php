<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Item\Validation;

/**
 * Validation routines for property "unitAmountWithoutVat".
 */
class UnitAmountWithoutVat extends AbstractValidation implements ValidationInterface
{
    /**
     * @var int
     */
    public const MIN_INTEGER_LENGTH = 0;

    /**
     * @var int
     */
    public const MAX_INTEGER_LENGTH = 15;

    /**
     * @var int
     */
    public const MIN_DECIMAL_LENGTH = 0;

    /**
     * @var int
     */
    public const MAX_DECIMAL_LENGTH = 5;

    /**
     * @inheritDoc
     *
     * NOTE: This is unsigned since payment items of type DISCOUNT expects a
     * negative value while ORDER_LINE and SHIPPING_FEE expects positive values.
     */
    public function validate(
        float $value = 0.0
    ): void {
        $this->hasFloatLength(
            $value,
            [
                'integer' => [
                    'min' => self::MIN_INTEGER_LENGTH,
                    'max' => self::MAX_INTEGER_LENGTH
                ],
                'decimal' => [
                    'min' => self::MIN_DECIMAL_LENGTH,
                    'max' => self::MAX_DECIMAL_LENGTH
                ]
            ]
        );
    }
}
