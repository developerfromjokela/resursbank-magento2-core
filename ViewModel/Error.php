<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\ViewModel;

use Resursbank\Core\ViewModel\Session\Checkout as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * View model for Resurs Bank's widget to fetch a customer's address based on
 * provided SSN or organisation number.
 */
class Error implements ArgumentInterface
{
    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Check whether payment failed.
     *
     * @return bool
     */
    public function paymentFailed(): bool
    {
        // Check if true (can be null)
        $result = $this->checkoutSession->getResursBankPaymentFailed() === true;
        if ($result === true) {
            // Unset the failure to avoid showing the message more than once
            $this->checkoutSession->setResursBankPaymentFailed(false);
        }
        return $result;
    }
}
