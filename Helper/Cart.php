<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Methods to handle and manipulate shopping cart.
 */
class Cart extends AbstractHelper
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepo;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepo;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param OrderRepositoryInterface $orderRepo
     * @param CartRepositoryInterface $quoteRepo
     * @param Context $context
     * @param Session $checkoutSession
     */
    public function __construct(
        OrderRepositoryInterface $orderRepo,
        CartRepositoryInterface $quoteRepo,
        Context $context,
        Session $checkoutSession
    ) {
        $this->orderRepo = $orderRepo;
        $this->quoteRepo = $quoteRepo;
        $this->checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    /**
     * Rebuilds the cart from an order.
     *
     * @param Order $order
     * @param bool $cancelOrder
     * @return bool
     * @throws NoSuchEntityException
     */
    public function rebuildCart(
        Order $order,
        bool $cancelOrder = true
    ): bool {
        $quote = $this->quoteRepo->get($order->getQuoteId());
        $result = false;

        if ($quote instanceof Quote) {
            $quote->setIsActive(1);

            $this->checkoutSession->replaceQuote($quote);
            $this->quoteRepo->save($quote);

            if ($cancelOrder) {
                $this->orderRepo->save($order->cancel());
            }

            $result = true;
        }

        return $result;
    }
}
