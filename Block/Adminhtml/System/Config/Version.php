<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
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
