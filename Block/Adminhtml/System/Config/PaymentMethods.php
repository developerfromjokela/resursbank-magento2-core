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
use Resursbank\Core\Helper\Scope;
use Resursbank\Ecom\Config as EcomConfig;
use Resursbank\Ecom\Module\PaymentMethod\Widget\PaymentMethods as Widget;
use Resursbank\Ecom\Module\Rco\Repository\PaymentMethods as Repository;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Throwable;

/**
 * Render widget utilised to reflect available payment methods.
 */
class PaymentMethods extends Field
{
    /**
     * @param Context $context
     * @param Log $log
     * @param Scope $scope
     * @param Config $config
     */
    public function __construct(
        Context $context,
        private readonly Log $log,
        private readonly Scope $scope,
        private readonly Config $config
    ) {
        $this->setTemplate(
            template: 'Resursbank_Core::system/config/payment-methods.phtml'
        );

        parent::__construct($context);
    }

    /**
     * Resolve widget content.
     *
     * @retrun string
     */
    public function getWidget(): string
    {
        $result = '';

        // Must have configured a store.
        $StoreId = $this->config->getStore(
            $this->scope->getId(),
            $this->scope->getType()
        );

        if ($StoreId === '') {
            return '';
        }

        try {
            EcomConfig::validateInstance();

            $widget = new Widget(
                paymentMethods: Repository::getPaymentMethods(storeId: $StoreId)
            );

            $result = $widget->content;
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return $result;
    }

    /**
     * Unset some non-related element parameters.
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(
        AbstractElement $element
    ): string {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
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
