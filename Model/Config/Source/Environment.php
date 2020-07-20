<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Resursbank\RBEcomPHP\ResursBank;

/**
 * Compile list of API environment alternatives.
 *
 * @package Resursbank\Core\Model\Config\Source
 */
class Environment extends Options
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            ResursBank::ENVIRONMENT_TEST => __('Test'),
            ResursBank::ENVIRONMENT_PRODUCTION => __('Production')
        ];
    }
}
