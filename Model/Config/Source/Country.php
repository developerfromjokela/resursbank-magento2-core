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
 * Options for country selection on configuration page.
 *
 * @package Resursbank\Core\Model\Config\Source
 */
class Country extends Options implements OptionSourceInterface
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
     * @inheritDoc
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
