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

namespace Resursbank\Core\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button as MagentoButton;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Exception\LocalizedException;
use Resursbank\Core\Helper\Config;

/**
 * Centralised code for buttons in admin config.
 *
 * @package Resursbank\Core\Block\Adminhtml\System\Config
 */
class Button extends Field
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     * @param Context $context
     */
    public function __construct(
        Config $config,
        Context $context
    ) {
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * Unset some non-related element parameters.
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Render and return button HTML.
     *
     * @param AbstractElement $element
     * @param string $label
     * @param string $path
     * @return string
     * @throws LocalizedException
     */
    public function create(
        AbstractElement $element,
        string $label,
        string $path
    ): string {
        $this->setElement($element);

        return $this->getLayout()
            ->createBlock(MagentoButton::class)
            ->setType('button')
            ->setClass('scalable')
            ->setLabel(__($label))
            ->setOnClick(
                "setLocation('{$this->config->buildUrl($path)}')"
            )
            ->toHtml();
    }
}
