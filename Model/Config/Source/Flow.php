<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options for flow selection on configuration page.
 *
 * @package Resursbank\Core\Model\Config\Source
 */
class Flow implements OptionSourceInterface
{

    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [];
    }

    /**
     * Get options in "key-value" format.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }
}
