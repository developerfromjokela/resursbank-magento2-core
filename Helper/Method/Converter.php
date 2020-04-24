<?php
/**
 * Copyright 2016 Resurs Bank AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper\Method;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\ValidatorException;
use Magento\Sales\Model\Order;

/**
 * Convert Resurs Bank API data of a payment method to an actual payment method
 * object which can be interpreted by Magento.
 *
 * @package Resursbank\Core\Helper
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
     * @var string
     */
    const MODEL_KEY_TITLE = 'description';

    /**
     * @var string
     */
    const MODEL_KEY_IDENTIFIER = 'identifier';

    /**
     * @var string
     */
    const MODEL_KEY_MIN_ORDER_TOTAL = 'min_order_total';

    /**
     * @var string
     */
    const MODEL_KEY_MAX_ORDER_TOTAL = 'max_order_total';

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
            self::MODEL_KEY_IDENTIFIER => $this->getIdentifier($data),
            self::MODEL_KEY_MIN_ORDER_TOTAL => $this->getMinLimit($data),
            self::MODEL_KEY_MAX_ORDER_TOTAL => $this->getMaxLimit($data),
            self::MODEL_KEY_TITLE => $this->getDescription($data)
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
