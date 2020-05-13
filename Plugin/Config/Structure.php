<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Config;

use Exception;
use Magento\Paypal\Model\Config\Structure\PaymentSectionModifier as Original;

/**
 * Create custom configuration sections for all dynamic payment methods.
 *
 * We need to create the sections this way since we do not know what payment
 * methods will be available until the client fetches them from the API.
 *
 * @package Resursbank\Core\Plugin\Config\Structure
 */
class Structure
{
    public function afterModify(
        Original $subject,
        array $result
    ): array {
        return $result;
    }
}
