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

namespace Resursbank\Checkout\Model\ResourceModel\Account;

use Resursbank\Checkout\Model\Account as Model;
use Resursbank\Checkout\Model\ResourceModel\Account as Resource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * API account resource collection.
 *
 * @package Resursbank\Checkout\Model\ResourceModel\Account
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize collection model.
     */
    protected function _construct(): void
    {
        $this->_init(Model::class, Resource::class);
    }
}
