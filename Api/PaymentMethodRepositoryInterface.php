<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Api\Data\PaymentMethodSearchResultsInterface;

interface PaymentMethodRepositoryInterface
{
    /**
     * Save (update / create) entry.
     *
     * @param PaymentMethodInterface $entry
     * @return PaymentMethodInterface
     */
    public function save(PaymentMethodInterface $entry): PaymentMethodInterface;

    /**
     * Get entry by ID.
     *
     * @param int|string $methodId
     * @return ?PaymentMethodInterface
     */
    public function get(int|string $methodId): ?PaymentMethodInterface;

    /**
     * Get entry by code.
     *
     * @param string $code
     * @return PaymentMethodInterface
     */
    public function getByCode(string $code): PaymentMethodInterface;

    /**
     * Retrieve entries matching the specified search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return PaymentMethodSearchResultsInterface
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): PaymentMethodSearchResultsInterface;

    /**
     * Delete entry.
     *
     * @param PaymentMethodInterface $entry
     * @return bool
     */
    public function delete(PaymentMethodInterface $entry): bool;

    /**
     * Delete entry by ID.
     *
     * @param int $methodId
     * @return bool
     */
    public function deleteById(int $methodId): bool;
}
