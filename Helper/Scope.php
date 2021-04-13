<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Extract specified scope type and id from request.
 */
class Scope extends AbstractHelper
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param Context $context
     * @param RequestInterface $request
     */
    public function __construct(
        Context $context,
        RequestInterface $request
    ) {
        $this->request = $request;

        parent::__construct($context);
    }

    /**
     * Retrieve scope type parameter name, if any.
     *
     * @return string
     */
    public function getTypeParam(): string
    {
        $result = '';

        if ($this->request->getParam('website') !== null) {
            $result = ScopeInterface::SCOPE_WEBSITE;
        } elseif ($this->request->getParam('store') !== null) {
            $result = ScopeInterface::SCOPE_STORE;
        }

        return $result;
    }

    /**
     * Retrieve scope type specified in request. If none, use default.
     *
     * @return string
     */
    public function getType(): string
    {
        $result = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        if ($this->request->getParam('website') !== null) {
            $result = ScopeInterface::SCOPE_WEBSITES;
        } elseif ($this->request->getParam('store') !== null) {
            $result = ScopeInterface::SCOPE_STORES;
        }

        return $result;
    }

    /**
     * Get scope id / code from request, if specified, otherwise return NULL.
     *
     * @param string|null $type
     * @return string|null
     */
    public function getId(
        ?string $type = null
    ): ?string {
        $type = $type ?? $this->getTypeParam();

        $value = $type !== '' ?
            $this->request->getParam($type ?? $this->getTypeParam()) :
            null;

        return is_numeric($value) ? (string) $value : null;
    }
}
