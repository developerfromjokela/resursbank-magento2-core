<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Block\Adminhtml\System\Config;

use Exception;
use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\UrlInterface;
use Resursbank\Core\Helper\Url;
use Resursbank\Ecom\Exception\ConfigException;
use Throwable;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\Helper\Scope;
use Resursbank\Ecom\Config as EcomConfig;
use Resursbank\Ecom\Module\PaymentMethod\Widget\PaymentMethods as PaymentMethodsWidget;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Magento\Framework\Data\Form\FormKey;

use function in_array;

/**
 * List payment methods and relevant metadata on config page.
 */
class GetStores extends Template
{
    /**
     * @param Context $context
     * @param Log $log
     * @param Url $url
     * @param Config $config
     * @param Scope $scope
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly Log $log,
        private readonly Url $url,
        private readonly Config $config,
        private readonly Scope $scope,
        array $data = []
    ) {
        if (!$this->usingMapi()) {
            return;
        }

        parent::__construct(
            context: $context,
            data: $data
        );
    }

    /**
     * @retrun string
     */
    public function getWidget(): string
    {
        try {
            $widget = new \Resursbank\Ecom\Module\Store\Widget\GetStores(
                fetchUrl: $this->url->getAdminUrl(path: 'resursbank_core/data/stores/form_key/' . $this->formKey->getFormKey()),
                storeSelectId: 'payment_other_resursbank_section_api_store',
                environmentSelectId: 'payment_other_resursbank_section_api_environment',
                automatic: false
            );

            return $widget->content;
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return '';
    }

    /**
     * Checks if we're using MAPI or not.
     *
     * @return bool
     */
    private function usingMapi(): bool
    {
        return $this->config->isMapiActive(
            scopeType: $this->scope->getType(),
            scopeCode: $this->scope->getId()
        );
    }
}
