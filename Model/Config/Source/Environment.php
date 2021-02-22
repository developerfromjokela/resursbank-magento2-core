<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Magento\Framework\Phrase;
use Resursbank\RBEcomPHP\ResursBank;

/**
 * Compile list of API environment alternatives.
 */
class Environment extends Options
{
    /**
     * @inheritDoc
     * @return array<int, Phrase>
     */
    public function toArray(): array
    {
        return [
            ResursBank::ENVIRONMENT_TEST => __('Test'),
            ResursBank::ENVIRONMENT_PRODUCTION => __('Production')
        ];
    }
}
