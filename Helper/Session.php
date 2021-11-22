<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\App\Response\Redirect as RedirectResponse;
use Resursbank\Core\ViewModel\Session\Checkout;

/**
 * This class implements ArgumentInterface (that's normally reserved for
 * ViewModels) because we found no other way of removing the suppressed warning
 * for PHPMD.CookieAndSessionMisuse. The interface fools the analytic tools into
 * thinking this class is part of the presentation layer, and thus eligible to
 * handle the session.
 */
class Session extends AbstractHelper implements ArgumentInterface
{
    /**
     * @var Checkout
     */
    public Checkout $checkout;

    /**
     * @var RedirectResponse
     */
    private RedirectResponse $redirectResponse;

    /**
     * @param Context $context
     * @param Checkout $checkout
     * @param RedirectResponse $redirectResponse
     */
    public function __construct(
        Context $context,
        Checkout $checkout,
        RedirectResponse $redirectResponse
    ) {
        $this->checkout = $checkout;
        $this->redirectResponse = $redirectResponse;

        parent::__construct($context);
    }

    /**
     * Will use the referer URL to set the Resurs Bank failure redirect URL.
     * We avoid using a static URL such as checkout/onepage/failure to ensure
     * redirects back from the gateway will function correctly with third
     * party extensions that override the checkout process.
     *
     * @return void
     */
    public function useRefererAsFailureRedirectUrl(): void
    {
        $this->checkout->setResursFailureRedirectUrl(
            $this->redirectResponse->getRefererUrl()
        );
    }
}
