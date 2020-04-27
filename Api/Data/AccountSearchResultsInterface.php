<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @package Resursbank\Core\Api\Data
 */
interface AccountSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get payment history list.
     *
     * @return AccountInterface[]
     */
    public function getItems(): array;

    /**
     * Set payment history list.
     *
     * @param AccountInterface[] $items
     * @return $this
     */
    public function setItems(
        array $items
    ): self;
}
