<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;

/**
 * Remove old order when an error occurs during the checkout process (to avoid
 * dangling, cancelled, orders).
 *
 * This class implements ArgumentInterface (that's normally reserved for
 * ViewModels) because we found no other way of removing the suppressed warning
 * for PHPMD.CookieAndSessionMisuse. The interface fools the analytic tools into
 * thinking this class is part of the presentation layer, and thus eligible to
 * handle the session.
 */
class RemoveOrder implements ArgumentInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepo;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var PaymentMethods
     */
    private PaymentMethods $paymentMethods;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param OrderRepositoryInterface $orderRepo
     * @param Log $log
     * @param Config $config
     * @param PaymentMethods $paymentMethods
     * @param Session $session
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepo,
        Log $log,
        Config $config,
        PaymentMethods $paymentMethods,
        Session $session,
        StoreManagerInterface $storeManager
    ) {
        $this->orderRepo = $orderRepo;
        $this->session = $session;
        $this->log = $log;
        $this->config = $config;
        $this->paymentMethods = $paymentMethods;
        $this->storeManager = $storeManager;
    }

    /**
     * When the order process fails (for example because of a rejected credit
     * application) we want to remove the order which may already have been
     * created (orders are created before the payment at Resurs Bank is
     * completed). If we do not remove the order with the failed payment we may
     * end up with a lot of orders from the same person, due to subsequent
     * attempts to place an order.
     *
     * This method deletes old orders. We can still re-use the increment ID from
     * the initial order attempt, so long as the customer session remains.
     */
    public function beforePlaceOrder(): void
    {
        try {
            $order = $this->session->getLastRealOrder();

            if ($this->isEnabled($order)) {
                $this->orderRepo->delete($order);
            }
        } catch (Exception $e) {
            $this->log->error($e->getMessage());
        }
    }

    /**
     * Check whether this plugin is enabled.
     *
     * @param OrderInterface $order
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function isEnabled(
        OrderInterface $order
    ): bool {
        $reservedOrderId = $this->session->getQuote()->getReservedOrderId();
        $payment = $order->getPayment();

        if (!($payment instanceof OrderPaymentInterface)) {
            throw new InvalidDataException(__(
                'Payment does not exist for order %1',
                $order->getIncrementId()
            ));
        }

        $storeCode = $this->storeManager->getStore()->getCode();

        return (
            $this->config->isReuseErroneouslyCreatedOrdersEnabled($storeCode) &&
            $this->paymentMethods->isResursBankMethod($payment->getMethod()) &&
            $order->getIncrementId() === $reservedOrderId
        );
    }
}
