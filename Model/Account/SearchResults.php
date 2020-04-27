<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Account;

use Magento\Framework\Api\SearchResults as FrameworkSearchResults;
use Resursbank\Core\Api\Data\AccountSearchResultsInterface;

/**
 * @package Resursbank\Core\Model\Account
 */
class SearchResults extends FrameworkSearchResults implements AccountSearchResultsInterface
{
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
