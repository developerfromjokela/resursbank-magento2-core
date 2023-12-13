<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Extract specified scope type and id from request or StoreManagerInterface,
 * depending whether you are on frontend or backend.
 */
class Scope
{
    private ?array $params = null;

    /**
     * @param RequestHttp $request
     * @param StoreManagerInterface $storeManager
     * @param AreaList $areaList
     */
    public function __construct(
        private readonly RequestHttp $request,
        private readonly StoreManagerInterface $storeManager,
        private readonly AreaList $areaList
    ) {
    }

    /**
     * Retrieve scope type parameter name, if any.
     *
     * @return string
     */
    public function getTypeParam(): string
    {
        $result = '';

        if ($this->getParam('website') !== null) {
            $result = ScopeInterface::SCOPE_WEBSITE;
        } elseif ($this->getParam('store') !== null) {
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
        if ($this->isFrontend()) {
            return ScopeInterface::SCOPE_STORES;
        }

        $result = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        if ($this->getParam('website') !== null) {
            $result = ScopeInterface::SCOPE_WEBSITES;
        } elseif ($this->getParam('store') !== null) {
            $result = ScopeInterface::SCOPE_STORES;
        }

        return $result;
    }

    /**
     * Get scope id / code from request, if specified, otherwise return NULL.
     *
     * @param string|null $type
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getId(
        ?string $type = null
    ): ?string {
        if ($this->isFrontend()) {
            return $this->storeManager->getStore()->getCode();
        }

        $type = $type ?? $this->getTypeParam();

        $value = $type !== '' ?
            $this->getParam($type ?? $this->getTypeParam()) :
            null;

        return is_numeric($value) ? (string) $value : null;
    }

    /**
     * Resolve parameter from request URI.
     *
     * ECom initiates before Magento has parsed request parameters from the URI.
     * ECom cannot initiate later because the cache kicks in. The method which
     * parses the request (\Magento\Framework\App\Router\Base::parseRequest) is
     * protected, so we cannot use it.
     *
     * This code should only be executed on the backend where an Order related
     * entity is not available to collect store information from.
     *
     * @param string $key
     * @return string|null
     */
    private function getParam(string $key): ?string
    {
        if ($this->params === null) {
            $request = (string) $this->request->getRequestUri();

            if (str_starts_with(haystack: $request, needle: '/')) {
                $request = substr(string: $request, offset: 1);
            }

            if (str_ends_with(haystack: $request, needle: '/')) {
                $request = substr(
                    string: $request,
                    offset: 0,
                    length: strlen(string: $request)-1
                );
            }

            $data = explode(separator: '/', string: $request);

            if (!empty($data)) {
                $chunks = array_chunk(array: $data, length: 2);

                $this->params = array_combine(
                    keys: array_column(array: $chunks, column_key: 0),
                    values: array_column(array: $chunks, column_key: 1)
                );
            }
        }

        return isset($this->params[$key]) ? (string) $this->params[$key] : null;
    }

    /**
     * Check if we are on frontend.
     *
     * @return bool
     */
    private function isFrontend(): bool
    {
        return $this->areaList->getCodeByFrontName(
            $this->request->getFrontName()
        ) === 'frontend';
    }
}
