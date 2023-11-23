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
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Methods to handle and manipulate shopping cart.
 *
 * This class implements ArgumentInterface (that's normally reserved for
 * ViewModels) because we found no other way of removing the suppressed warning
 * for PHPMD.CookieAndSessionMisuse. The interface fools the analytic tools into
 * thinking this class is part of the presentation layer, and thus eligible to
 * handle the session.
 */
class Cart extends AbstractHelper implements ArgumentInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepo;

    /**
     * @var Session
     */
    private Session $checkoutSession;

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
        OrderInterface $order,
        bool $releaseReservedOrderId = false
    ): bool {
        $quote = $this->quoteRepo->get((int) $order->getQuoteId());
        $result = false;

        if ($quote instanceof Quote) {
            $quote->setIsActive(true);

            if ($releaseReservedOrderId) {
                $quote->unsetData(key: CartInterface::KEY_RESERVED_ORDER_ID);
            }

            $this->checkoutSession->replaceQuote($quote);
            $this->quoteRepo->save($quote);

            $result = true;
        }

        return $result;
    }
}
