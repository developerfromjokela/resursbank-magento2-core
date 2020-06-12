<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Item\Validation;

/**
 * Validation routines for property "quantity".
 */
class Quantity extends AbstractValidation implements ValidationInterface
{
    /**
     * @var int
     */
    private const MIN_INTEGER_LENGTH = 0;

    /**
     * @var int
     */
    private const MAX_INTEGER_LENGTH = 15;

    /**
     * @var int
     */
    private const MIN_DECIMAL_LENGTH = 0;

    /**
     * @var int
     */
    private const MAX_DECIMAL_LENGTH = 5;

    /**
     * @inheritDoc
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

        $this->isPositiveNumber($value);
    }
}
