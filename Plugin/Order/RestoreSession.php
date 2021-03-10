<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Session\SuccessValidator;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Helper\Request;

/**
 * Restores the checkout session based on relevant values gathered from a quote
 * and its corresponding order.
 *
 * If the session has been lost during the signing process (likely due to
 * switching browsers), we need to restore specific session values to ensure
 * Magento handles the success / failure procedure correctly.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class RestoreSession
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var SuccessValidator
     */
    private $successValidator;

    /**
     * @var Session
     */
    private $session;
    /**
     * @var Order
     */
    private $order;

    /**
     * @param Log $log
     * @param Request $request
     * @param Order $order
     * @param Session $session
     * @param SuccessValidator $successValidator
     */
    public function __construct(
        Log $log,
        Request $request,
        Order $order,
        Session $session,
        SuccessValidator $successValidator
    ) {
        $this->log = $log;
        $this->request = $request;
        $this->order = $order;
        $this->session = $session;
        $this->successValidator = $successValidator;
    }

    /**
     * @return void
     */
    public function beforeExecute(): void
    {
        try {
            if (!$this->successValidator->isValid()) {
                $quoteId = $this->request->getQuoteId();
                $order = $this->order->getOrderByQuoteId($quoteId);

                /** @noinspection PhpUndefinedMethodInspection */
                /** @phpstan-ignore-next-line */
                $this->session
                    ->setLastQuoteId($quoteId)
                    ->setLastSuccessQuoteId($quoteId)
                    ->setLastOrderId($order->getEntityId())
                    ->setLastRealOrderId($order->getIncrementId());
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }
    }
}
