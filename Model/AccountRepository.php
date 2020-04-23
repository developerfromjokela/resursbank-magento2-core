<?php
/**
 * Copyright 2016 Resurs Bank AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Resursbank\Checkout\Model;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Resursbank\Checkout\Api\Data\AccountInterface;
use Resursbank\Checkout\Api\Data\AccountSearchResultsInterface;
use Resursbank\Checkout\Api\Data\AccountSearchResultsInterfaceFactory;
use Resursbank\Checkout\Api\AccountRepositoryInterface;
use Resursbank\Checkout\Model\AccountFactory;
use Resursbank\Checkout\Model\ResourceModel\Account as ResourceModel;
use Resursbank\Checkout\Model\ResourceModel\Account\CollectionFactory;

/**
 * Repository for payment history event entries.
 *
 * @package Resursbank\Checkout\Model
 */
class AccountRepository implements AccountRepositoryInterface
{
    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @var AccountSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var FilterProcessor
     */
    private $filterProcessor;

    /**
     * @param ResourceModel $resourceModel
     * @param AccountFactory $accountFactory
     * @param AccountSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param FilterProcessor $filterProcessor
     */
    public function __construct(
        ResourceModel $resourceModel,
        AccountFactory $accountFactory,
        AccountSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        FilterProcessor $filterProcessor
    ) {
        $this->resourceModel = $resourceModel;
        $this->accountFactory = $accountFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->filterProcessor = $filterProcessor;
    }

    /**
     * @inheritDoc
     * @throws AlreadyExistsException
     * @throws Exception
     */
    public function save(
        AccountInterface $entry
    ): AccountInterface {
        $this->resourceModel->save($entry);

        return $entry;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function delete(AccountInterface $entry): bool
    {
        $this->resourceModel->delete($entry);

        return true;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $Id): bool
    {
        return $this->delete($this->get($Id));
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function get(int $id): AccountInterface
    {
        $history = $this->accountFactory->create();
        $history->getResource()->load($history, $id);

        if (!$history->getId()) {
            throw new NoSuchEntityException(
                __('Unable to find payment history entry with ID %1', $id)
            );
        }

        return $history;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): AccountSearchResultsInterface {
        $collection = $this->collectionFactory->create();

        $this->filterProcessor->process($searchCriteria, $collection);

        $collection->load();

        return $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria)
            ->setItems($collection->getItems())
            ->setTotalCount($collection->getSize());
    }
}
