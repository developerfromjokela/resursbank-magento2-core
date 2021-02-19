<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PaymentMethodSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Returns a list of database entries as a result of a database search.
     *
     * @return PaymentMethodInterface[]
     */
    public function getItems(): array;

    /**
     * Set list of items to search through.
     *
     * @param PaymentMethodInterface[] $items
     * @return self
     */
    public function setItems(array $items): self;
}
