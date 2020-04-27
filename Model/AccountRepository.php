<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model;

use Exception;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Resursbank\Core\Api\AccountRepositoryInterface;
use Resursbank\Core\Api\Data\AccountCollectionInterfaceFactory;
use Resursbank\Core\Api\Data\AccountInterface;
use Resursbank\Core\Api\Data\AccountInterfaceFactory;
use Resursbank\Core\Api\Data\AccountSearchResultsInterface;
use Resursbank\Core\Api\Data\AccountSearchResultsInterfaceFactory;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\Core\Model\ResourceModel\Account as ResourceModel;
use Resursbank\Core\Model\ResourceModel\Account\Collection;

/**
 * @package Resursbank\Core\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountRepository implements AccountRepositoryInterface
{
    /**
     * @noinspection PhpUndefinedClassInspection
     * @var AccountInterfaceFactory
     */
    protected $accountFactory;

    /**
     * @noinspection PhpUndefinedClassInspection
     * @var AccountSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * @noinspection PhpUndefinedClassInspection
     * @var AccountCollectionInterfaceFactory
     */
    private $collectionFactory;

    /**
     * @var FilterProcessor
     */
    private $filterProcessor;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @noinspection PhpUndefinedClassInspection
     * @param ResourceModel $resourceModel
     * @param AccountInterfaceFactory $accountFactory
     * @param AccountSearchResultsInterfaceFactory $searchResultsFactory
     * @param AccountCollectionInterfaceFactory $collectionFactory
     * @param FilterProcessor $filterProcessor
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchBuilder
     */
    public function __construct(
        ResourceModel $resourceModel,
        AccountInterfaceFactory $accountFactory,
        AccountSearchResultsInterfaceFactory $searchResultsFactory,
        AccountCollectionInterfaceFactory $collectionFactory,
        FilterProcessor $filterProcessor,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchBuilder
    ) {
        $this->resourceModel = $resourceModel;
        $this->accountFactory = $accountFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->filterProcessor = $filterProcessor;
        $this->filterBuilder = $filterBuilder;
        $this->searchBuilder = $searchBuilder;
    }

    /**
     * @inheritDoc
     * @throws AlreadyExistsException
     * @throws Exception
     */
    public function save(AccountInterface $entry): AccountInterface
    {
        /** @var Account $entry */
        $this->resourceModel->save($entry);

        return $entry;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function delete(AccountInterface $entry): bool
    {
        /** @var Account $entry */
        $this->resourceModel->delete($entry);

        return true;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function deleteById(int $id): bool
    {
        return $this->delete($this->get($id));
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function get(int $id): AccountInterface
    {
        /**
         * @var Account $result
         * @noinspection PhpUndefinedMethodInspection
         */
        $result = $this->accountFactory->create();

        $this->resourceModel->load($result, $id);

        if (!$result->getId()) {
            throw new NoSuchEntityException(
                __('Unable to find account with ID %1', $id)
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): AccountSearchResultsInterface {
        /**
         * @var Collection $collection
         * @noinspection PhpUndefinedMethodInspection
         */
        $collection = $this->collectionFactory->create();

        $this->filterProcessor->process($searchCriteria, $collection);

        $collection->load();

        /** @noinspection PhpUndefinedMethodInspection */
        return $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria)
            ->setItems($collection->getItems())
            ->setTotalCount($collection->getSize());
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getByCredentials(Credentials $credentials): ?AccountInterface
    {
        $account = null;
        $filterUsername = $this->filterBuilder
            ->setField('username')
            ->setValue($credentials->getUsername())
            ->create();

        $filterEnvironment= $this->filterBuilder
            ->setField('environment')
            ->setValue($credentials->getEnvironment())
            ->create();

        $searchCriteria = $this->searchBuilder
            ->addFilters([$filterUsername, $filterEnvironment])
            ->create();

        $result = $this->getList($searchCriteria);

        if ($result->getTotalCount() > 0) {
            $items = $result->getItems();
            $account = end($items);
        }

        return $account;
    }
}
