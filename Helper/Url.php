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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;

class Url extends AbstractHelper
{
    /**
     * @param Context $context
     * @param AdminUrlInterface $adminUrl
     * @param UrlInterface $url
     * @param Config $config
     * @param Scope $scope
     */
    public function __construct(
        Context $context,
        private readonly AdminUrlInterface $adminUrl,
        private readonly UrlInterface $url,
        private readonly Config $config,
        private readonly Scope $scope
    ) {
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
     * Fetch success page URL.
     *
     * This is the URL the client is redirected back to after successfully
     * completing their payment at the gateway.
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
     * Fetch failure page URL.
     *
     * This is the URL the client is redirected back to after failing to
     * completing their payment at the gateway.
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

    /**
     * Construct a URL for external services to communicate with this website.
     *
     * @param string $route
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getExternalUrl(
        string $route
    ): string {
        $store = $this->scope->getStoreManager()->getStore(
            storeId: $this->scope->getId()
        );

        $params = [
            '_secure' => true,
            '_scope' => $store,
            '_scope_to_url' => true
        ];

        $url = $store->getUrl(route: $route, params: $params);

        if ($this->config->isDeveloperModeActive(
            scopeCode: $store->getCode()
        )) {
            $url .= '?XDEBUG_SESSION=' . $this->config->getXdebugSessionValue(
                scopeCode: $store->getCode()
            );
        }

        return $url;
    }
}
