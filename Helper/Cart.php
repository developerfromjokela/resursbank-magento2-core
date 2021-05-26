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
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Methods to handle and manipulate shopping cart.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Cart extends AbstractHelper
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepo;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param CartRepositoryInterface $quoteRepo
     * @param Context $context
     * @param Session $checkoutSession
     */
    public function __construct(
        CartRepositoryInterface $quoteRepo,
        Context $context,
        Session $checkoutSession
    ) {
        $this->quoteRepo = $quoteRepo;
        $this->checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    /**
     * Rebuilds the cart from an order.
     *
     * @param OrderInterface $order
     * @return bool
     * @throws NoSuchEntityException
     */
    public function rebuildCart(
        OrderInterface $order
    ): bool {
        $quote = $this->quoteRepo->get((int) $order->getQuoteId());
        $result = false;

        if ($quote instanceof Quote) {
            $quote->setIsActive(true);

            $this->checkoutSession->replaceQuote($quote);
            $this->quoteRepo->save($quote);

            $result = true;
        }

        return $result;
    }
}
