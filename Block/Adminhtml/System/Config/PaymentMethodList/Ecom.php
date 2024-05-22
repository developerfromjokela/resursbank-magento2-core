<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Block\Adminhtml\System\Config\PaymentMethodList;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Resursbank\Core\Helper\Ecom as EcomHelper;
use Resursbank\Core\Helper\PaymentMethods\Ecom as PaymentMethodsHelper;
use Resursbank\Core\Helper\Scope;
use Resursbank\Ecom\Config as EcomConfig;
use Resursbank\Ecom\Module\PaymentMethod\Widget\PaymentMethods as Widget;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Throwable;

/**
 * Render widget utilised to reflect available payment methods.
 */
class Ecom extends Field
{
    /**
     * @param Context $context
     * @param Log $log
     * @param Scope $scope
     * @param Config $config
     * @param EcomHelper $ecom
     * @param PaymentMethodsHelper $paymentMethodsHelper
     */
    public function __construct(
        Context $context,
        private readonly Log $log,
        private readonly Scope $scope,
        private readonly Config $config,
        private readonly EcomHelper $ecom,
        private readonly PaymentMethodsHelper $paymentMethodsHelper
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

        try {
            // Must have configured a store.
            $storeId = $this->config->getStore(
                $this->scope->getId(),
                $this->scope->getType()
            );

            if ($storeId === '' ||
                !$this->ecom->canConnect(
                    scopeCode: $this->scope->getId(),
                    scopeType: $this->scope->getType()
                )
            ) {
                return '';
            }

            EcomConfig::validateInstance();

            $widget = new Widget(
                paymentMethods: $this->paymentMethodsHelper->getPaymentMethodsCollection(
                    storeId: $storeId,
                    scopeCode: $this->scope->getId(),
                    scopeType: $this->scope->getType()
                )
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
