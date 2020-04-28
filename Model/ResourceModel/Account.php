<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * @package Resursbank\Core\Model\ResourceModel
 */
class Account extends AbstractDb
{
    /**
     * Initialize resource model.
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void
    {
        $this->_init('resursbank_checkout_account', 'account_id');
    }
}
