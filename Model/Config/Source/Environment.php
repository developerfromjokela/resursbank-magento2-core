<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Compile list of API environment alternatives.
 *
 * @package Resursbank\Core\Model\Config\Source
 */
class Environment extends Options implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'test' => __('Test'),
            'production' => __('Production')
        ];
    }
}
