<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper\PaymentMethods;

use JsonException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\ValidatorException;
use Magento\Sales\Model\Order;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use function is_string;

/**
 * Convert payment method data from the Resurs Bank API to data which can be
 * interpreted by our Magento module.
 */
class Converter extends AbstractHelper
{
    /**
     * @var string
     */
    public const KEY_DESCRIPTION = 'description';

    /**
     * @var string
     */
    public const KEY_ID = 'id';

    /**
     * @var string
     */
    public const KEY_MIN_LIMIT = 'minLimit';

    /**
     * @var string
     */
    public const KEY_MAX_LIMIT = 'maxLimit';

    /**
     * @var string
     */
    public const DEFAULT_DESCRIPTION = 'Resurs Bank Payment';

    /**
     * @var float
     */
    public const DEFAULT_MIN_LIMIT = 150.0;

    /**
     * @var float
     */
    public const DEFAULT_MAX_LIMIT = 0.0;

    /**
     * @var string
     */
    public const DEFAULT_VALUE_ORDER_STATUS = Order::STATE_PENDING_PAYMENT;

    /**
     * Converts data to format Magento is able to parse.
     *
     * @param array<mixed> $data
     * @return array<mixed>
     * @throws ValidatorException
     * @throws JsonException
     */
    public function convert(
        array $data
    ): array {
        // Validate provided data.
        if (!$this->validate($data)) {
            throw new ValidatorException(
                __(
                    'rb-data-conversion-failed-provided-data-is-invalid',
                    json_encode($data, JSON_THROW_ON_ERROR)
                )
            );
        }

        // Convert to data Magento can interpret and return.
        return [
            PaymentMethodInterface::IDENTIFIER => $this->getIdentifier($data),
            PaymentMethodInterface::MIN_ORDER_TOTAL => $this->getMinLimit($data),
            PaymentMethodInterface::MAX_ORDER_TOTAL => $this->getMaxLimit($data),
            PaymentMethodInterface::TITLE => $this->getDescription($data),
            PaymentMethodInterface::RAW => json_encode($data, JSON_THROW_ON_ERROR)
        ];
    }

    /**
     * Validate data.
     *
     * @param array<mixed> $data
     * @return bool
     */
    public function validate(
        array $data
    ): bool {
        return $this->getIdentifier($data) !== null;
    }

    /**
     * Get method identifier.
     *
     * @param array<mixed> $data
     * @return string
     */
    public function getIdentifier(
        array $data
    ): ?string {
        return (
            isset($data[self::KEY_ID]) &&
            is_string($data[self::KEY_ID]) &&
            $data[self::KEY_ID] !== ''
        ) ? $data[self::KEY_ID] : null;
    }

    /**
     * Get method description.
     *
     * @param array<mixed> $data
     * @return string
     */
    public function getDescription(
        array $data
    ): string {
        return (
            isset($data[self::KEY_DESCRIPTION]) &&
            is_string($data[self::KEY_DESCRIPTION]) &&
            $data[self::KEY_DESCRIPTION] !== ''
        ) ? $data[self::KEY_DESCRIPTION] : self::DEFAULT_DESCRIPTION;
    }

    /**
     * Get method minimum limit.
     *
     * @param array<mixed> $data
     * @return float
     */
    public function getMinLimit(
        array $data
    ): float {
        return (
            isset($data[self::KEY_MIN_LIMIT]) &&
            is_numeric($data[self::KEY_MIN_LIMIT])
        ) ? (float) $data[self::KEY_MIN_LIMIT] : self::DEFAULT_MIN_LIMIT;
    }

    /**
     * Get method maximum limit.
     *
     * @param array<mixed> $data
     * @return float
     */
    public function getMaxLimit(
        array $data
    ): float {
        return (
            isset($data[self::KEY_MAX_LIMIT]) &&
            is_numeric($data[self::KEY_MAX_LIMIT])
        ) ? (float) $data[self::KEY_MAX_LIMIT] : self::DEFAULT_MAX_LIMIT;
    }
}
