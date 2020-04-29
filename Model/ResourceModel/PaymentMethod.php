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
class PaymentMethod extends AbstractDb
{
    /**
     * Initialize resource model.
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void
    {
        $this->_init('resursbank_checkout_account_method', 'method_id');
    }
}
