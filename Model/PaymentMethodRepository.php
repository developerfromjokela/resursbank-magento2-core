<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterfaceFactory;
use Resursbank\Core\Api\Data\PaymentMethodSearchResultsInterface;
use Resursbank\Core\Api\Data\PaymentMethodSearchResultsInterfaceFactory;
use Resursbank\Core\Api\PaymentMethodRepositoryInterface;
use Resursbank\Core\Model\ResourceModel\PaymentMethod as ResourceModel;
use Resursbank\Core\Model\ResourceModel\PaymentMethod\CollectionFactory;

/**
 * @package Resursbank\Core\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    /**
     * @var PaymentMethodInterfaceFactory
     */
    protected $methodFactory;

    /**
     * @var PaymentMethodSearchResultsInterfaceFactory
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
     * @param PaymentMethodInterfaceFactory $methodFactory
     * @param PaymentMethodSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param FilterProcessor $filterProcessor
     */
    public function __construct(
        ResourceModel $resourceModel,
        PaymentMethodInterfaceFactory $methodFactory,
        PaymentMethodSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        FilterProcessor $filterProcessor
    ) {
        $this->resourceModel = $resourceModel;
        $this->methodFactory = $methodFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->filterProcessor = $filterProcessor;
    }

    /**
     * @inheritDoc
     * @throws AlreadyExistsException
     * @throws Exception
     */
    public function save(PaymentMethodInterface $entry): PaymentMethodInterface
    {
        /** @var PaymentMethod $entry */
        $this->resourceModel->save($entry);

        return $entry;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function delete(PaymentMethodInterface $entry): bool
    {
        /** @var PaymentMethod $entry */
        $this->resourceModel->delete($entry);

        return true;
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $methodId): bool
    {
        return $this->delete($this->get($methodId));
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function get(int $methodId): PaymentMethodInterface
    {
        /** @var PaymentMethod $result */
        $result = $this->methodFactory->create();

        $this->resourceModel->load($result, $methodId);

        if (!$result->getId()) {
            throw new NoSuchEntityException(
                __('Unable to find payment method with ID %1', $methodId)
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function getByCode(string $code): PaymentMethodInterface
    {
        /** @var PaymentMethod $result */
        $result = $this->methodFactory->create();

        $this->resourceModel->load(
            $result,
            $code,
            PaymentMethodInterface::CODE
        );

        if (!$result->getId()) {
            throw new NoSuchEntityException(
                __('Unable to find payment method with code %1', $code)
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): PaymentMethodSearchResultsInterface {
        $collection = $this->collectionFactory->create();

        $this->filterProcessor->process($searchCriteria, $collection);

        $collection->load();

        return $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria)
            ->setItems($collection->getItems())
            ->setTotalCount($collection->getSize());
    }
}
