<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Item\Validation;

/**
 * Validation routines for property "artNo".
 */
class ArtNo extends AbstractValidation implements ValidationInterface
{
    /**
     * Regex defining allowed characters.
     *
     * NOTE: The regex is reversed for simplified use with preg_replace to clean
     * your values. The validating preg_match uses the same regex, again in
     * reverse, to validate the value you've provided.
     *
     * @var string
     */
    private const REGEX = '/[^a-z0-9]/';

    /**
     * @var int
     */
    private const MIN_LENGTH = 1;

    /**
     * @var int
     */
    private const MAX_LENGTH = 100;

    /**
     * @inheritDoc
     */
    public function validate(
        string $value = ''
    ): void {
        $this->matchesRegex($value, self::REGEX);
        $this->hasStringLength($value, [
            'min' => self::MIN_LENGTH,
            'max' => self::MAX_LENGTH
        ]);
    }
}
