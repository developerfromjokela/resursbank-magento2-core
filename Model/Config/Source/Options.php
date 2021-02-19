<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Phrase;

/**
 * Generic methods for option collections mainly utilised for select elements
 * within the configuration.
 */
abstract class Options implements OptionSourceInterface
{
    /**
     * Returns a list of options formatted to function with select elements in
     * the admin configuration.
     *
     * @return array<array>
     */
    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = compact(['value', 'label']);
        }

        return $result;
    }

    /**
     * Returns an associative array of options formatted as 'value' => 'label'.
     *
     * @return array<int|string, string|Phrase>
     */
    public function toArray(): array
    {
        return [];
    }
}
