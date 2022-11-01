<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

use Exception;
use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\ViewModel\Session\Checkout as Session;

/**
 * Restores the checkout session based on relevant values gathered from a quote
 * and its corresponding order.
 *
 * If the session has been lost during the signing process (likely due to
 * switching browsers), we need to restore specific session values to ensure
 * Magento handles the success / failure procedure correctly.
 *
 * This class implements ArgumentInterface (that's normally reserved for
 * ViewModels) because we found no other way of removing the suppressed warning
 * for PHPMD.CookieAndSessionMisuse. The interface fools the analytic tools into
 * thinking this class is part of the presentation layer, and thus eligible to
 * handle the session.
 */
class RestoreSession implements ArgumentInterface
{
    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var SuccessValidator
     */
    private SuccessValidator $successValidator;

    /**
     * @var Session
     */
    private Session $session;
    /**
     * @var Order
     */
    private Order $order;

    /**
     * @param Log $log
     * @param Order $order
     * @param Session $session
     * @param SuccessValidator $successValidator
     */
    public function __construct(
        Log $log,
        Order $order,
        Session $session,
        SuccessValidator $successValidator,
        PaymentMethods $paymentMethods
    ) {
        $this->log = $log;
        $this->order = $order;
        $this->session = $session;
        $this->successValidator = $successValidator;
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @return void
     */
    public function beforeExecute(): void
    {
        try {
            if (!$this->successValidator->isValid()) {
                $order = $this->order->resolveOrderFromRequest();
                $resursbankResult = $this->order->getResursbankResult($order);

                if (!$order->getPayment()) {
                    throw new Exception('Payment is null');
                }
                $isResursMethod = $this->paymentMethods->isResursBankMethod(
                    code: $order->getPayment()->getMethod()
                );

                if (!$resursbankResult && $isResursMethod) {
                    $quoteId = $order->getQuoteId();
                    $this->session
                        ->setLastQuoteId($quoteId)
                        ->setLastSuccessQuoteId($quoteId)
                        ->setLastOrderId($order->getEntityId())
                        ->setLastRealOrderId($order->getIncrementId());
                }
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }
    }
}
