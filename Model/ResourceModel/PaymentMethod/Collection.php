<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\ResourceModel\PaymentMethod;

use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Model\PaymentMethod as Model;
use Resursbank\Core\Model\ResourceModel\PaymentMethod as Resource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * API account resource collection.
 *
 * @package Resursbank\Core\Model\ResourceModel\PaymentMethod
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize collection model.
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void
    {
        $this->_init(Model::class, Resource::class);
    }

    /**
     * @return PaymentMethodInterface[]
     */
    public function getItems(): array
    {
        $result = parent::getItems();

        return is_array($result) ? $result : [];
    }
}
