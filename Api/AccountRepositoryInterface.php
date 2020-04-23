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

namespace Resursbank\Checkout\Api;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Resursbank\Checkout\Api\Data\AccountInterface;
use Resursbank\Checkout\Api\Data\AccountSearchResultsInterface;

/**
 * Payment history CRUD interface.
 *
 * This interface specifies rules to manipulate / retrieve payment history
 * event entries.
 */
interface AccountRepositoryInterface
{
    /**
     * Save (update / create) entry.
     *
     * @param AccountInterface $entry
     * @return AccountInterface
     * @throws Exception
     * @throws AlreadyExistsException
     */
    public function save(
        AccountInterface $entry
    ): AccountInterface;

    /**
     * Get entry by ID.
     *
     * @param int $id
     * @return AccountInterface
     * @throws LocalizedException
     */
    public function get(int $id): AccountInterface;

    /**
     * Retrieve entries matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return AccountSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): AccountSearchResultsInterface;

    /**
     * Delete entry.
     *
     * @param AccountInterface $entry
     * @return bool
     * @throws LocalizedException
     */
    public function delete(AccountInterface $entry): bool;

    /**
     * Delete entry by ID.
     *
     * @param int $id
     * @return bool
     * @throws LocalizedException
     */
    public function deleteById(int $id): bool;
}
