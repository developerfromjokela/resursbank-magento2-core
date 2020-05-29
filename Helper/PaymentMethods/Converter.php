<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper\PaymentMethods;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\ValidatorException;
use Magento\Sales\Model\Order;
use Resursbank\Core\Api\Data\PaymentMethodInterface;

/**
 * Convert payment method data from the Resurs Bank API to data which can be
 * interpreted by our Magento module.
 *
 * @package Resursbank\Core\Helper\PaymentMethods
 */
class Converter extends AbstractHelper
{
    /**
     * @var string
     */
    const KEY_DESCRIPTION = 'description';

    /**
     * @var string
     */
    const KEY_ID = 'id';

    /**
     * @var string
     */
    const KEY_MIN_LIMIT = 'minLimit';

    /**
     * @var string
     */
    const KEY_MAX_LIMIT = 'maxLimit';

    /**
     * @var string
     */
    const DEFAULT_DESCRIPTION = 'Resurs Bank Payment';

    /**
     * @var float
     */
    const DEFAULT_MIN_LIMIT = 150.0;

    /**
     * @var float
     */
    const DEFAULT_MAX_LIMIT = 0.0;

    /**
     * @var string
     */
    const DEFAULT_VALUE_ORDER_STATUS = Order::STATE_PENDING_PAYMENT;

    /**
     * @param array $data
     * @return array
     * @throws ValidatorException
     */
    public function convert(array $data): array
    {
        // Validate provided data.
        if (!$this->validate($data)) {
            throw new ValidatorException(
                __(
                    'Data conversion failed. Provided data is invalid. %1',
                    json_encode($data)
                )
            );
        }

        // Convert to data Magento can interpret and return.
        return [
            PaymentMethodInterface::IDENTIFIER => $this->getIdentifier($data),
            PaymentMethodInterface::MIN_ORDER_TOTAL => $this->getMinLimit($data),
            PaymentMethodInterface::MAX_ORDER_TOTAL => $this->getMaxLimit($data),
            PaymentMethodInterface::TITLE => $this->getDescription($data),
            PaymentMethodInterface::RAW => json_encode($data)
        ];
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validate(array $data): bool
    {
        return $this->getIdentifier($data) !== null;
    }

    /**
     * @param array $data
     * @return string
     */
    public function getIdentifier(array $data): ?string
    {
        return (
            isset($data[self::KEY_ID]) &&
            is_string($data[self::KEY_ID]) &&
            $data[self::KEY_ID] !== ''
        ) ? $data[self::KEY_ID] : null;
    }

    /**
     * @param array $data
     * @return string
     */
    public function getDescription(array $data): string
    {
        return (
            isset($data[self::KEY_DESCRIPTION]) &&
            is_string($data[self::KEY_DESCRIPTION]) &&
            $data[self::KEY_DESCRIPTION] !== ''
        ) ? $data[self::KEY_DESCRIPTION] : self::DEFAULT_DESCRIPTION;
    }

    /**
     * @param array $data
     * @return float
     */
    public function getMinLimit(array $data): float
    {
        return (
            isset($data[self::KEY_MIN_LIMIT]) &&
            is_numeric($data[self::KEY_MIN_LIMIT])
        ) ? (float) $data[self::KEY_MIN_LIMIT] : self::DEFAULT_MIN_LIMIT;
    }

    /**
     * @param array $data
     * @return float
     */
    public function getMaxLimit(array $data): float
    {
        return (
            isset($data[self::KEY_MAX_LIMIT]) &&
            is_numeric($data[self::KEY_MAX_LIMIT])
        ) ? (float) $data[self::KEY_MAX_LIMIT] : self::DEFAULT_MAX_LIMIT;
    }
}
