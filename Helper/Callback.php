<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Callback helper.
 */
class Callback extends AbstractHelper
{
    /**
     * @param Context $context
     * @param RequestInterface $request
     */
    public function __construct(
        Context $context,
        private readonly RequestInterface $request
    ) {
        parent::__construct(context: $context);
    }

    /**
     * Retrieve callback URL.
     *
     * @param StoreInterface $store
     * @param string $type
     * @param string $suffix
     * @return string
     */
    public function getUrl(
        StoreInterface $store,
        string $type,
        string $suffix = ''
    ): string {
        return (
            $store->getBaseUrl(
                type: UrlInterface::URL_TYPE_LINK,
                secure: $this->request->isSecure()
            ) . "rest/V1/resursbank_ordermanagement/order/$type$suffix"
        );
    }
}
