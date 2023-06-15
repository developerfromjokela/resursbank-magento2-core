<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\ResourceModel\PaymentMethod;

use Resursbank\Core\Model\PaymentMethod as Model;
use Resursbank\Core\Model\ResourceModel\PaymentMethod as Resource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initializes object.
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function _construct(): void
    {
        $this->_init(model: Model::class, resourceModel: Resource::class);
    }
}
