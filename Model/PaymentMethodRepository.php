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
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    /**
     * @param ResourceModel $resourceModel
     * @param PaymentMethodInterfaceFactory $methodFactory
     * @param PaymentMethodSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param FilterProcessor $filterProcessor
     * @param StringValidation $stringValidation
     */
    public function __construct(
        private readonly ResourceModel $resourceModel,
        private readonly PaymentMethodInterfaceFactory $methodFactory,
        private readonly PaymentMethodSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly FilterProcessor $filterProcessor,
        private readonly StringValidation $stringValidation
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws AlreadyExistsException
     * @throws Exception
     */
    public function save(
        PaymentMethodInterface $entry
    ): PaymentMethodInterface {
        /** @var PaymentMethod $entry */
        $this->resourceModel->save($entry);

        return $entry;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function delete(
        PaymentMethodInterface $entry
    ): bool {
        /** @var PaymentMethod $entry */
        $this->resourceModel->delete($entry);

        return true;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function deleteById(
        int $methodId
    ): bool {
        return $this->delete($this->get($methodId));
    }

    /**
     * @inheritDoc
     *
     * @throws NoSuchEntityException
     */
    public function get(
        string|int $methodId
    ): PaymentMethodInterface {
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
     */
    public function getByCode(
        string $code
    ): PaymentMethodInterface {
        /** @var PaymentMethod $result */
        $result = $this->methodFactory->create();

        $this->resourceModel->load(
            object: $result,
            value: $code,
            field: PaymentMethodInterface::CODE
        );

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
