<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Item\Validation;

use InvalidArgumentException;
use JsonException;
use function in_array;
use function is_array;
use function strlen;

/**
 * Basic validation routines.
 */
abstract class AbstractValidation
{
    /**
     * Retrieve name of child class performing validation to reflect the
     * property being validated. This is useful to supply more informative
     * Exception messages.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return lcfirst(substr((string) strrchr(static::class, "\\"), 1));
    }

    /**
     * NOTE: Regex rules expects a reverse value (meaning that if preg_match
     * succeeds there are illegal characters within the provided $value). This
     * is because the regex values defined in our child classes SHOULD be used
     * to sanitize values before they are submitted to this validation method.
     *
     * @param string $value
     * @param string $regex Regex with illegal pattern(s).
     * @return void
     * @throws InvalidArgumentException
     */
    public function matchesRegex(
        string $value,
        string $regex
    ): void {
        if (preg_match($regex, $value)) {
            throw new InvalidArgumentException(
                'Regex validation failed for ' . $this->getSubject() .
                ' using regex ' . $regex . ' and value ' . $value
            );
        }
    }

    /**
     * Validate string length. If there is no "min" or "max" defined these are
     * set to int(0).
     *
     * NOTE: int(0) values are treated as unlimited length.
     *
     * @param string $value
     * @param array<mixed> $length
     * @return void
     * @throws InvalidArgumentException
     */
    public function hasStringLength(
        string $value,
        array $length
    ): void {
        $min = !isset($length['min']) ? 0 : (int) $length['min'];
        $max = !isset($length['max']) ? 0 : (int) $length['max'];
        $strLen = strlen($value);

        if (($min !== 0 && $strLen < $min) || ($max !== 0 && $strLen > $max)) {
            throw new InvalidArgumentException(
                'String length validation failed for ' . $this->getSubject() .
                '. Min length ' . $min . ', max length ' . $max . ', value ' .
                $value . ' (with length ' . $strLen . ')'
            );
        }
    }

    /**
     * Validate float length (number of integer and/or decimal digits).
     *
     * NOTE: Float lengths are difficult to validate in PHP since numbers will
     * sooner or later be rounded. You should not have any problems as long as
     * the number of characters (including the separating "." for your decimals)
     * does not exceed 15 characters. For example, a value like
     * "999999999.99999" shouldn't incur any rounding while an additional
     * integer or decimal digit would.
     *
     * @param float $value
     * @param array<mixed> $length
     * @return void
     * @link https://www.php.net/manual/en/language.types.float.php
     * @noinspection OffsetOperationsInspection
     */
    public function hasFloatLength(
        float $value,
        array $length
    ): void {
        $pieces = explode('.', (string) $value);

        if (is_array($pieces)) {
            if (isset($pieces[0], $length['integer'])) {
                $this->hasStringLength($pieces[0], $length['integer']);
            }

            if (isset($pieces[1], $length['decimal'])) {
                $this->hasStringLength($pieces[1], $length['decimal']);
            }
        }
    }

    /**
     * Validate that a number is positive.
     *
     * @param float $value
     * @return void
     * @throws InvalidArgumentException
     */
    public function isPositiveNumber(
        float $value
    ): void {
        if ($value < 0) {
            throw new InvalidArgumentException(
                'Validation of ' . $this->getSubject() . ' failed. Value ' .
                'must be positive, ' . $value . ' given.'
            );
        }
    }

    /**
     * Validate that a number is negative.
     *
     * @param float $value
     * @return void
     * @throws InvalidArgumentException
     */
    public function isNegativeNumber(
        float $value
    ): void {
        if ($value > 0) {
            throw new InvalidArgumentException(
                'Validation of ' . $this->getSubject() . ' failed. Value ' .
                'must be negative, ' . $value . ' given.'
            );
        }
    }

    /**
     * Validate the provided $value is included in $list of allowed values.
     *
     * @param mixed $value
     * @param array<mixed> $list
     * @return void
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function isOneOf(
        $value,
        array $list
    ): void {
        if (!in_array($value, $list, true)) {
            throw new InvalidArgumentException(
                'Validation of ' . $this->getSubject() . ' failed. Value ' .
                'must be on of ' . json_encode($list, JSON_THROW_ON_ERROR)
            );
        }
    }
}
