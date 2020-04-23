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

namespace Resursbank\Core\Block\Adminhtml\System\Config;

use \Resursbank\Core\Helper\Version as Helper;
use \Magento\Config\Block\System\Config\Form\Field;
use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Collect version numbers and display on config page.
 *
 * @package Resursbank\Core\Block\Adminhtml\System\Config
 */
class Version extends Field
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Context $context
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        $this->setTemplate('system/config/version.phtml');

        parent::__construct($context, $data);
    }

    /**
     * Unset some non-related element parameters.
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Get the button and scripts contents.
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Retrieve instance of helper class that contains methods to collect the
     * various module versions from database / filesystem.
     *
     * @return Helper
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
