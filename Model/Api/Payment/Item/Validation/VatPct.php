<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Item\Validation;

/**
 * Validation routines for property "vatPct".
 */
class VatPct extends AbstractValidation implements ValidationInterface
{

    /**
     * Only allow these specific values.
     *
     * @var string[]
     */
    public const ALLOWED_VALUES = [
        0, 6, 12, 25, 8, 15, 10, 14, 24, 25.5
    ];

    /**
     * @inheritDoc
     */
    public function validate(
        float $value = 0
    ): void {
        $this->isOneOf($value, self::ALLOWED_VALUES);

        $this->isPositiveNumber($value);
    }
}
