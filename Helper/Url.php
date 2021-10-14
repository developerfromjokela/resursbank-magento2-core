<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Backend\Model\UrlInterface as AdminUrlInterface;
use Magento\Framework\UrlInterface;

class Url extends AbstractHelper
{
    /**
     * @var AdminUrlInterface
     */
    private AdminUrlInterface $adminUrl;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @param Context $context
     * @param AdminUrlInterface $adminUrl
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        AdminUrlInterface $adminUrl,
        UrlInterface $url
    ) {
        $this->adminUrl = $adminUrl;
        $this->url = $url;

        parent::__construct($context);
    }

    /**
     * Return URL to admin page.
     *
     * @param string $path
     * @return string
     */
    public function getAdminUrl(
        string $path
    ): string {
        return $this->adminUrl->getUrl(
            $path,
            [
                '_secure' => $this->_getRequest()->isSecure(),
                'store' => $this->_getRequest()->getParam('store'),
                'website' => $this->_getRequest()->getParam('website')
            ]
        );
    }

    /**
     * URL client is redirect back to after successfully completing their
     * payment at the gateway.
     *
     * NOTE: We include quote id to support intermediate browser change during
     * signing. For example, if the client signs their payment using BankID on a
     * smartphone the redirect URL may be opened in the OS default browser
     * instead of the browser utilised by the customer to perform their
     * purchase. This means the session data is lost and the order will fail.
     * By including this parameter we can load the data back into the session
     * if it's missing.
     *
     * @param int $quoteId
     * @return string
     */
    public function getSuccessUrl(
        int $quoteId
    ): string {
        return $this->url->getUrl(
            'checkout/onepage/success',
            ['quote_id' => $quoteId]
        );
    }

    /**
     * URL client is redirected back to after failing to completing their
     * payment at the gateway.
     *
     * NOTE: For information regarding the included quote id parameter, please
     * refer to the getSuccessUrl() docblock above.
     *
     * @param int $quoteId
     * @return string
     */
    public function getFailureUrl(
        int $quoteId
    ): string {
        return $this->url->getUrl(
            'checkout/onepage/failure',
            ['quote_id' => $quoteId]
        );
    }

    /**
     * Retrieve the URL we redirect clients to after rebuilding the cart (after
     * they reach the failure page).
     * Override is used when a customer has a different checkout url than magento standard.
     *
     * @param string|null $override
     * @return string
     */
    public function getCheckoutRebuildRedirectUrl(?string $override): string
    {
        return $this->url->getUrl($override ?? 'checkout');
    }
}
