<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Item\Validation;

/**
 * Validation routines for property "description".
 */
class Description extends AbstractValidation implements ValidationInterface
{
    /**
     * @var int
     */
    private const MIN_LENGTH = 1;

    /**
     * @var int
     */
    private const MAX_LENGTH = 255;

    /**
     * @inheritDoc
     */
    public function validate(
        string $value = ''
    ): void {
        $this->hasStringLength(
            $value,
            [
                'min' => self::MIN_LENGTH,
                'max' => self::MAX_LENGTH
            ]
        );
    }
}
