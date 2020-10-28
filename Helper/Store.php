<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\StoreNotFoundException;

/**
 * Provides business logic for handling store specific operations.
 */
class Store extends AbstractHelper
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param Log $log
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Log $log,
        RequestInterface $request,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->log = $log;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getActiveStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }

    /**
     * Get the current store from request.
     *
     * @return StoreInterface
     * @throws StoreNotFoundException
     */
    public function fromRequest(): StoreInterface
    {
        $store = null;

        try {
            $storeId = (int) $this->request->getParam('store');

            $store = $storeId > 0 ?
                $this->storeManager->getStore($storeId) :
                $store = $this->storeManager->getStore();
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        if (!($store instanceof StoreInterface)) {
            throw new StoreNotFoundException('Failed to obtain store.');
        }

        return $store;
    }
}
