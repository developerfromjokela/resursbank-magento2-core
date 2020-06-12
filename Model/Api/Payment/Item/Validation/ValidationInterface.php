<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment\Item\Validation;

use Exception;
use InvalidArgumentException;

/**
 * Requirements for property validation.
 */
interface ValidationInterface
{
    /**
     * NOTE: all child classes are expected to define values to be validated
     * as optional argument(s) for this method. Since their datatype(s) may
     * vary, arguments cannot be defined within this interface.
     *
     * @return void
     * @link https://test.resurs.com/docs/display/ecom/Hosted+payment+flow+data
     * @throws Exception When something unexpected happens.
     * @throws InvalidArgumentException When validation fails.
     */
    public function validate(): void;
}
