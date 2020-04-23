<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Environment
 *
 * @package Resursbank\Core\Model\Config\Source
 */
class Environment implements OptionSourceInterface
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'test',
                'label' => __('Test')
            ],
            [
                'value' => 'production',
                'label' => __('Production')
            ]
        ];
    }

    /**
     * Get options in "key-value" format.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'test' => __('Test'),
            'production' => __('Production')
        ];
    }
}
