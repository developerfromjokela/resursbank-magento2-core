<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Block\Adminhtml\System\Config\Methods;

use Exception;
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

use function in_array;

/**
 * List payment methods and relevant metadata on config page.
 */
class Listing extends Field
{
    /**
     * @param Context $context
     * @param PaymentMethods $paymentMethods
     * @param Log $log
     * @param PriceCurrencyInterface $priceCurrency
     * @param RequestInterface $request
     * @param Scope $scope
     * @param Config $config
     * @param array<mixed> $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        private PaymentMethods $paymentMethods,
        private Log $log,
        private PriceCurrencyInterface $priceCurrency,
        private RequestInterface $request,
        private Scope $scope,
        private Config $config,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->paymentMethods = $paymentMethods;
        $this->log = $log;
        $this->priceCurrency = $priceCurrency;
        $this->request = $request;
        $this->scope = $scope;
        $this->config = $config;

        if ($this->usingMapi()) {
            $this->setTemplate(template: 'system/config/methods/ecomlisting.phtml');
        } else {
            $this->setTemplate('system/config/methods/listing.phtml');
        }

        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * Fetches an array of payment methods.
     *
     * @return PaymentMethodInterface[]
     */
    public function getMethods(): array
    {
        $result = [];

        try {
            $result = $this->paymentMethods->getMethodsByCredentials(
                $this->scope->getId(),
                $this->scope->getType()
            );
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Loads the payment method widget from Ecom.
     *
     * @return string
     * @throws ConfigException
     */
    public function getEcomWidget(): string
    {
        if (!$this->usingMapi()) {
            return '<h1>Attempted to load MAPI payment method listing but MAPI does not appear to be active</h1>';
        }

        try {
            $widget = new PaymentMethodsWidget(
                paymentMethods: Repository::getPaymentMethods(
                    storeId: $this->config->getStore(
                        scopeType: $this->scope->getType(),
                        scopeCode: $this->scope->getId()
                    )
                )
            );
            return $widget->content;
        } catch (Throwable $error) {
            EcomConfig::getLogger()->error(message: $error);
            return '<h1>' . __('rb-payment-methods-widget-render-failed') . ': ' . $error->getMessage() . '</h1>';
        }
    }

    /**
     * Formats a price to include decimals and the configured currency of the
     * selected store.
     *
     * Example: 123.53 => "123.53,00 kr"
     *
     * @param float $price
     * @return string
     */
    public function formatPrice(
        float $price
    ): string {
        $result = number_format(
            $price,
            PriceCurrencyInterface::DEFAULT_PRECISION
        );

        try {
            $result = $this->priceCurrency->format(
                $price,
                false,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $this->request->getParam('store', 0)
            );
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Could not use method prefix 'get' because of a magic method signature.
     *
     * @param PaymentMethodInterface $method
     * @return string
     * @phpstan-ignore-next-line Incompatible magic Magento getter.
     */
    public function getOrderMinTotal(
        PaymentMethodInterface $method
    ): string {
        return $this->showMinMax($method)
            ? $this->formatPrice((float) $method->getMinOrderTotal())
            : '';
    }

    /**
     * Could not use method prefix 'get' because of a magic method signature.
     *
     * @param PaymentMethodInterface $method
     * @return string
     * @phpstan-ignore-next-line Incompatible magic Magento getter.
     */
    public function getOrderMaxTotal(
        PaymentMethodInterface $method
    ): string {
        return $this->showMinMax($method)
            ? $this->formatPrice((float) $method->getMaxOrderTotal())
            : '';
    }

    /**
     * Only show Min & Max for methods that have a type that is not
     * CARD or PAYMENT_PROVIDER.
     *
     * @param PaymentMethodInterface $method
     * @return bool
     */
    public function showMinMax(
        PaymentMethodInterface $method
    ): bool {
        return !in_array(
            $method->getType(),
            ['CARD', 'PAYMENT_PROVIDER']
        );
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
     * Get HTML content.
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

    /**
     * Checks if we're using MAPI or not.
     *
     * @return bool
     */
    private function usingMapi(): bool
    {
        if (
            $this->config->isMapiActive(
                scopeType: $this->scope->getType(),
                scopeCode: $this->scope->getId()
            )
        ) {
            return true;
        }

        return false;
    }
}
