<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Block\Adminhtml\System\Config\Methods;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Phrase;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

/**
 * List payment methods and relevant metadata on config page.
 */
class Listing extends Field
{
    /**
     * @var PaymentMethods
     */
    private $paymentMethods;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param Context $context
     * @param PaymentMethods $paymentMethods
     * @param Log $log
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        PaymentMethods $paymentMethods,
        Log $log,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->paymentMethods = $paymentMethods;
        $this->log = $log;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->request = $request;

        $this->setTemplate('system/config/methods/listing.phtml');

        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * @return PaymentMethodInterface[]
     */
    public function getMethods(): array
    {
        $result = [];

        try {
            return $this->paymentMethods->getMethodsByCredentials();
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
                $this->storeManager->getStore(
                    $this->request->getParam('store', 0)
                )
            );
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * @param PaymentMethodInterface $method
     * @return Phrase
     */
    public function getMin(
        PaymentMethodInterface $method
    ): Phrase {
        return __(
            'Minimum order total %1',
            $this->formatPrice(
                $method->getMinOrderTotal()
            )
        );
    }

    /**
     * @param PaymentMethodInterface $method
     * @return Phrase
     */
    public function getMax(
        PaymentMethodInterface $method
    ): Phrase {
        return __(
            'Maximum order total %1',
            $this->formatPrice(
                $method->getMaxOrderTotal()
            )
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
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _getElementHtml(
        AbstractElement $element
    ): string {
        return $this->_toHtml();
    }
}
