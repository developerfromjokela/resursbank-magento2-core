<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Resursbank\Ecom\Module\SupportInfo\Widget\SupportInfo as Widget;
use Magento\Framework\Module\PackageInfo;
use Resursbank\Core\Helper\Log;
use Throwable;

/**
 * Displays the SupportInfo widget from Ecom.
 */
class SupportInfo extends Field
{
    /** @var null|Widget $widget */
    public readonly ?Widget $widget;

    /**
     * @param Context $context
     * @param Log $log
     * @param PackageInfo $packageInfo
     */
    public function __construct(
        Context $context,
        private readonly Log $log,
        private readonly PackageInfo $packageInfo
    ) {
        $this->setTemplate(
            template: 'Resursbank_Core::system/config/support-info.phtml'
        );

        parent::__construct($context);

        $this->renderWidget();
    }

    /**
     * Render widget to properties.
     *
     * @return void
     */
    private function renderWidget(): void
    {
        try {
            $version = $this->getVersion();
            $this->widget = new Widget(pluginVersion: $version);
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }
    }

    /**
     * Get module version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        try {
            return 'Resursbank_Core: ' . $this->packageInfo->getVersion(
                moduleName: 'Resursbank_Core'
            );
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return '';
    }

    /**
     * Get widget content.
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function _getElementHtml(
        AbstractElement $element
    ): string {
        return $this->_toHtml();
    }
}
