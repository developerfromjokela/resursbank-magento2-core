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

namespace Resursbank\Checkout\Model\Account;

use Magento\Framework\Api\SearchResults as FrameworkSearchResults;
use Resursbank\Checkout\Api\Data\AccountSearchResultsInterface;

class SearchResults extends FrameworkSearchResults implements AccountSearchResultsInterface
{
    /**
     * @inheritDoc
     */
    public function getItems(): array
    {
        return parent::getItems();
    }

    /**
     * @inheritDoc
     */
    public function setItems(
        array $items
    ): AccountSearchResultsInterface {
        parent::setItems($items);

        return $this;
    }
}
