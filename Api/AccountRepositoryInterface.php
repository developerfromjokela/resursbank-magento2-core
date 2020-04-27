<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Resursbank\Core\Api\Data\AccountInterface;
use Resursbank\Core\Api\Data\AccountSearchResultsInterface;
use Resursbank\Core\Model\Api\Credentials;

/**
 * @package Resursbank\Core\Api
 */
interface AccountRepositoryInterface
{
    /**
     * Save (update / create) entry.
     *
     * @param AccountInterface $entry
     * @return AccountInterface
     */
    public function save(AccountInterface $entry): AccountInterface;

    /**
     * Get entry by ID.
     *
     * @param int $accountId
     * @return AccountInterface
     */
    public function get(int $accountId): AccountInterface;

    /**
     * Retrieve entries matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return AccountSearchResultsInterface
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): AccountSearchResultsInterface;

    /**
     * Delete entry.
     *
     * @param AccountInterface $entry
     * @return bool
     */
    public function delete(AccountInterface $entry): bool;

    /**
     * Delete entry by ID.
     *
     * @param int $accountId
     * @return bool
     */
    public function deleteById(int $accountId): bool;

    /**
     * Get an account entry by its credentials.
     *
     * @param Credentials $credentials
     * @return AccountInterface|null - Returns null if an entry couldn't be
     * found.
     */
    public function getByCredentials(
        Credentials $credentials
    ): ?AccountInterface;
}
