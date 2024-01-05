<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Backend\App\Router;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Extract specified scope type and id from request or StoreManagerInterface,
 * depending whether you are on frontend or backend.
 *
 * You will notice we intentionally avoid using DI, this is to ensure
 * compatibility with future Magento releases which may alter the DI of the
 * Router class which we are forced to extend. We are forced to extend it
 * because we need access to the parseRequest method in order to resolve
 * request parameters in the expected way (taking such things are URL rewrites
 * into account).
 */
class Scope extends Router
{
    /**
     * @var StoreManagerInterface|null
     */
    private ?StoreManagerInterface $storeManager = null;

    /**
     * @var RequestInterface|null
     */
    private ?RequestInterface $request = null;

    /**
     * @var AreaList|null
     */
    private ?AreaList $areaList = null;

    /**
     * @var array|null
     */
    private ?array $parameters = null;

    /**
     * Resolve ObjectManager instance.
     *
     * We intentionally avoid DI, see class comment.
     *
     * @return ObjectManager
     */
    private function getObjectManager(): ObjectManager
    {
        return ObjectManager::getInstance();
    }

    /**
     * Resolve store manager object.
     *
     * We intentionally avoid DI, see class comment.
     *
     * @return StoreManagerInterface
     */
    public function getStoreManager(): StoreManagerInterface
    {
        if ($this->storeManager === null) {
            $this->storeManager = $this->getObjectManager()->create(
                type: StoreManagerInterface::class
            );
        }

        return $this->storeManager;
    }

    /**
     * Resolve HTTP request object.
     *
     * We intentionally avoid DI, see class comment.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        if ($this->request === null) {
            $this->request = $this->getObjectManager()->create(
                type: RequestInterface::class
            );
        }

        return $this->request;
    }

    /**
     * Resolve area list.
     *
     *  We intentionally avoid DI, see class comment.
     *
     * @return AreaList
     */
    public function getAreaList(): AreaList
    {
        if ($this->areaList === null) {
            $this->areaList = $this->getObjectManager()->create(
                type: AreaList::class
            );
        }

        return $this->areaList;
    }

    /**
     * Resolve parameters from request and store in $this->>parameters
     *
     * @return array
     */
    private function getParameters(): array
    {
        if ($this->parameters === null) {
            $data = $this->parseRequest(request: $this->getRequest());

            $this->parameters =
                (
                    isset($data['variables']) &&
                    is_array($data['variables'])
                ) ? $data['variables'] : [];
        }

        return $this->parameters;
    }

    /**
     * Resolve specific request parameter value.
     *
     * @param string $key
     * @return string|null
     */
    private function getParam(string $key): ?string
    {
        $data = $this->getParameters();

        return isset($data[$key]) ? (string) $data[$key] : null;
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
            return $this->getStoreManager()->getStore()->getCode();
        }

        $type = $type ?? $this->getTypeParam();

        $value = $type !== '' ?
            $this->getParam($type ?? $this->getTypeParam()) :
            null;

        return is_numeric($value) ? (string) $value : null;
    }

    /**
     * Check if we are on frontend.
     *
     * @return bool
     */
    private function isFrontend(): bool
    {
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        return $this->getAreaList()->getCodeByFrontName(
                $this->getRequest()->getFrontName()
            ) !== 'adminhtml';
    }
}
