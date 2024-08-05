<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Block\Adminhtml\System\Config\Methods;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\Helper\Scope;
use function in_array;

/**
 * List payment methods and relevant metadata on config page.
 */
class Listing extends Field
{
    /**
     * @var PaymentMethods
     */
    private PaymentMethods $paymentMethods;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var PriceCurrencyInterface
     */
    private PriceCurrencyInterface $priceCurrency;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var Scope
     */
    private Scope $scope;

    /**
     * @param Context $context
     * @param PaymentMethods $paymentMethods
     * @param Log $log
     * @param PriceCurrencyInterface $priceCurrency
     * @param RequestInterface $request
     * @param Scope $scope
     * @param array<mixed> $data
     */
    public function __construct(
        Context $context,
        PaymentMethods $paymentMethods,
        Log $log,
        PriceCurrencyInterface $priceCurrency,
        RequestInterface $request,
        Scope $scope,
        array $data = []
    ) {
        $this->paymentMethods = $paymentMethods;
        $this->log = $log;
        $this->priceCurrency = $priceCurrency;
        $this->request = $request;
        $this->scope = $scope;

        $this->setTemplate('system/config/methods/listing.phtml');

        parent::__construct($context, $data);
    }

    /**
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
}
