<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\ResourceModel\Account;

use Resursbank\Core\Model\Account as Model;
use Resursbank\Core\Model\ResourceModel\Account as Resource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * API account resource collection.
 *
 * @package Resursbank\Core\Model\ResourceModel\Account
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
}
