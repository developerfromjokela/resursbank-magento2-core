<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
<<<<<<< Updated upstream
=======
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\RequestInterface;
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Helper\Cart as CartHelper;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;
use Magento\Checkout\Controller\Onepage\Failure;

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
<<<<<<< Updated upstream
<<<<<<< Updated upstream
     * @var StoreManagerInterface
     */
    private $storeManager;
=======
     * @var RequestInterface
     */
    private $request;
>>>>>>> Stashed changes
=======
     * @var RequestInterface
     */
    private $request;
>>>>>>> Stashed changes

    /**
     * @param ManagerInterface $messageManager
     * @param Log $log
     * @param UrlInterface $url
     * @param RedirectFactory $redirectFactory
     * @param Session $checkoutSession
     * @param CartHelper $cartHelper
     * @param Config $config
     * @param PaymentMethods $paymentMethods
<<<<<<< Updated upstream
<<<<<<< Updated upstream
     * @param StoreManagerInterface $storeManager
=======
     * @param RequestInterface $request
>>>>>>> Stashed changes
=======
     * @param RequestInterface $request
>>>>>>> Stashed changes
     */
    public function __construct(
        ManagerInterface $messageManager,
        Log $log,
        UrlInterface $url,
        RedirectFactory $redirectFactory,
        Session $checkoutSession,
        CartHelper $cartHelper,
<<<<<<< Updated upstream
<<<<<<< Updated upstream
        Config $config,
        PaymentMethods $paymentMethods,
        StoreManagerInterface $storeManager
=======
        PaymentMethods $paymentMethods,
        RequestInterface $request
>>>>>>> Stashed changes
=======
        PaymentMethods $paymentMethods,
        RequestInterface $request
>>>>>>> Stashed changes
    ) {
        $this->messageManager = $messageManager;
        $this->log = $log;
        $this->url = $url;
        $this->redirectFactory = $redirectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->cartHelper = $cartHelper;
        $this->config = $config;
        $this->paymentMethods = $paymentMethods;
<<<<<<< Updated upstream
<<<<<<< Updated upstream
        $this->storeManager = $storeManager;
=======
        $this->request = $request;
>>>>>>> Stashed changes
=======
        $this->request = $request;
>>>>>>> Stashed changes
    }

    /**
     * @param Failure $subject
     * @param Page|Redirect $result
     * @return Page|Redirect
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterExecute(
        Failure $subject,
        $result
    ) {
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

                // Redirect to cart page.
                $result = $this->redirectFactory->create()->setPath(
                    $this->url->getUrl('checkout/cart')
                );
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__(
                'The payment failed and the cart could not be rebuilt. ' .
                'Please add the items back to your cart manually and try ' .
                'a different payment alternative. We sincerely apologize ' .
                'for this inconvenience.'
            ));

            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Whether or not this plugin should execute.
     *
     * @param OrderInterface $order
     * @return bool
     * @throws InvalidDataException
     */
    private function isEnabled(
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
            (int) $this->request->getParam('disable_rebuild_cart') !== 1 &&
            $this->paymentMethods->isResursBankMethod($payment->getMethod())
        );
    }
}
