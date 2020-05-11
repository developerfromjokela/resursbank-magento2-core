<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\PaymentMethod;

use Magento\Framework\Api\SearchResults as FrameworkSearchResults;
use Resursbank\Core\Api\Data\PaymentMethodSearchResultsInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterface;

/**
 * @package Resursbank\Core\Model\Account
 */
class SearchResults extends FrameworkSearchResults implements PaymentMethodSearchResultsInterface
{
    /**
     * Returns a list of database entries as a result of a database search.
     *
     * This method is necessary to suppress warnings and provide better
     * debugging information during development. Without it, an array of
     * Magento\Framework\Api\AbstractExtensibleObject[] will be returned which
     * is not helpful when all we want to retrieve are entries that adhere to
     * the PaymentMethodInterface.
     *
     * @inheritDoc
     */
    public function getItems(): array
    {
        /** @var PaymentMethodInterface[] $result */
        $result = parent::getItems();

        return is_array($result) ? $result : [];
    }

    /**
     * @inheritDoc
     */
    public function setItems(
        array $items
    ): PaymentMethodSearchResultsInterface {
        parent::setItems($items);

        return $this;
    }
}
