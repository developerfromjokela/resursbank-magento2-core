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
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterfaceFactory;
use Resursbank\Core\Api\Data\PaymentMethodSearchResultsInterface;
use Resursbank\Core\Api\Data\PaymentMethodSearchResultsInterfaceFactory;
use Resursbank\Core\Api\PaymentMethodRepositoryInterface;
use Resursbank\Core\Helper\Config;
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
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ResourceModel $resourceModel,
        private readonly PaymentMethodInterfaceFactory $methodFactory,
        private readonly PaymentMethodSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly FilterProcessor $filterProcessor,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
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
     *
     * @throws NoSuchEntityException
     */
    public function getByCode(
        string $code
    ): PaymentMethodInterface {
        /** @var PaymentMethod $result */
        $result = $this->methodFactory->create();
        $scopeCode = $this->storeManager->getStore()->getCode();
        $flow = $this->config->getFlow(scopeCode: $scopeCode);

        $this->resourceModel->load(
            object: $result,
            value: $code,
            field: PaymentMethodInterface::CODE
        );

        // If the code is a UUID, despite the fact that $flow can be a legacy based method, we should not
        // avoid verifying the flow as something else that rcoplus. Main reason is that all payment methods
        // are always iterated even if the flow may be unsupported.
        // @todo Can this be optimized?
        if (!$this->isUuid(code: $code) && $flow !== 'rcoplus' && !$result->getId()) {
            /** @noinspection PhpArgumentWithoutNamedIdentifierInspection */
            throw new NoSuchEntityException(
                __('Unable to find payment method with code %1', $code)
            );
        }

        return $result;
    }

    /**
     * Validate if the code is an actual UUID after the "resursbank_" string.
     * @param string $code
     * @return bool
     */
    private function isUuid(string $code): bool
    {
        try {
            if (str_starts_with(haystack: $code, needle: 'resursbank_')) {
                $uuidCodeTest = preg_replace('/^resursbank_/', '', $code);
                return $this->stringValidation->isUuid(value: $uuidCodeTest);
            }
        } catch (Throwable) {
        }

        return false;
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
