<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Indicates a problem with data transmitted between the server and the API.
 *
 * @package Resursbank\Core\Exception
 */
class ApiDataException extends LocalizedException
{

}
