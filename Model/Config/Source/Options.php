<?php
/**
 * Copyright 2016 Resurs Bank AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Generic methods for option collections mainly utilised for select elements
 * within the configuration.
 *
 * @package Resursbank\Core\Model\Config\Source
 */
abstract class Options implements OptionSourceInterface
{
    /**
     * Returns a list of options formatted to function with select elements in
     * the admin configuration.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $result;
    }

    /**
     * Returns an associative array of options formatted as 'value' => 'label'.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }
}
