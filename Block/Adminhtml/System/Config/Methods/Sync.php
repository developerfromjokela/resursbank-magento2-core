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

namespace Resursbank\Core\Block\Adminhtml\System\Config\Methods;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Resursbank\Core\Block\Adminhtml\System\Config\Button;

/**
 * Render button to sync payment methods.
 *
 * @package Resursbank\Core\Block\Adminhtml\System\Config\Methods
 */
class Sync extends Button
{
    /**
     * Retrieve button HTML.
     *
     * @param AbstractElement $element
     * @return string
     * @throws LocalizedException
     * @codingStandardsIgnoreStart (suppress unavoidable PHPCS warnings)
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        // @codingStandardsIgnoreEnd
        return $this->create(
            $element,
            'Sync',
            'resursbank_checkout/methods/sync'
        );
    }
}
