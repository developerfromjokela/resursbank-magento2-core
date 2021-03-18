<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

namespace Resursbank\Core\Plugin\Order;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Helper\Cart as CartHelper;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;

/**
 * Cancel the previous order, rebuild the cart and redirect to the cart.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RebuildCart
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartHelper
     */
    private $cartHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PaymentMethods
     */
    private $paymentMethods;

    /**
     * @param ManagerInterface $messageManager
     * @param Log $log
     * @param UrlInterface $url
     * @param RedirectFactory $redirectFactory
     * @param Session $checkoutSession
     * @param CartHelper $cartHelper
     * @param Config $config
     * @param PaymentMethods $paymentMethods
     */
    public function __construct(
        ManagerInterface $messageManager,
        Log $log,
        UrlInterface $url,
        RedirectFactory $redirectFactory,
        Session $checkoutSession,
        CartHelper $cartHelper,
        Config $config,
        PaymentMethods $paymentMethods
    ) {
        $this->messageManager = $messageManager;
        $this->log = $log;
        $this->url = $url;
        $this->redirectFactory = $redirectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->cartHelper = $cartHelper;
        $this->config = $config;
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @return Redirect
     * @throws Exception
     */
    public function afterExecute(): Redirect
    {
        $redirect = $this->redirectFactory->create();

        try {
            $order = $this->checkoutSession->getLastRealOrder();

            if ($this->isEnabled($order)) {
                $this->cartHelper->rebuildCart($order);

                // Add error message explaining the payment failed but they may
                // try a different payment method.
                $this->messageManager->addErrorMessage(__(
                    'The payment failed. Please confirm the cart content ' .
                    'and try a different payment method.'
                ));
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__(
                'The payment failed and the cart could not be rebuilt. ' .
                'Please add the items back to your cart manually and try ' .
                'a different payment alternative. We sincerely apologize ' .
                'for this inconvenience.'
            ));

            $this->log->error($e);
        }

        // Redirect to cart page.
        return $redirect->setPath($this->url->getUrl('checkout/cart'));
    }

    /**
     * @param OrderInterface $order
     * @return bool
     * @throws InvalidDataException
     */
    public function isEnabled(
        OrderInterface $order
    ): bool {
        $payment = $order->getPayment();

        if (!($payment instanceof OrderPaymentInterface)) {
            throw new InvalidDataException(__(
                'Payment does not exist for order %1',
                $order->getIncrementId()
            ));
        }

        return (
            $this->config->isReuseErroneouslyCreatedOrdersEnabled() &&
            $this->paymentMethods->isResursBankMethod($payment->getMethod())
        );
    }
}
