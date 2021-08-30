<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\ViewModel;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * View model for Resurs Bank's widget to fetch a customer's address based on
 * provided SSN or organisation number.
 */
class Error implements ArgumentInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Check whether payment failed.
     *
     * @return bool
     */
    public function paymentFailed(): bool
    {
        return (int) $this->request->getParam(
            'resursbank_payment_failed'
        ) === 1;
    }
}
