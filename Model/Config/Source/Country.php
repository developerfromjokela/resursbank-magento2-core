<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options for country selection on configuration page.
 *
 * @package Resursbank\Core\Model\Config\Source
 */
class Country implements OptionSourceInterface
{
    /**
     * @var string
     */
    const SWEDEN = 'SE';

    /**
     * @var string
     */
    const NORWAY = 'NO';

    /**
     * @var string
     */
    const FINLAND = 'FI';

    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::SWEDEN,
                'label' => __('Sweden')
            ],
            [
                'value' => self::NORWAY,
                'label' => __('Norway')
            ],
            [
                'value' => self::FINLAND,
                'label' => __('Finland')
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
            self::SWEDEN => __('Sweden'),
            self::NORWAY => __('Norway'),
            self::FINLAND => __('Finland')
        ];
    }
}
