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

namespace Resursbank\Checkout\Model;

use Magento\Framework\Model\AbstractModel;
use Resursbank\Checkout\Model\Api\Credentials;
use Resursbank\Checkout\Model\ResourceModel\Account as Resource;
use Resursbank\Checkout\Model\ResourceModel\Account\Collection;
use Resursbank\Checkout\Model\ResourceModel\Account\CollectionFactory;

/**
 * API account.
 *
 * @package Resursbank\Checkout\Model
 */
class Account extends AbstractModel
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Initialize model.
     */
    protected function _construct(
        CollectionFactory $collectionFactory
    ) {
        $this->_init(Resource::class);

        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Load by Credentials instance.
     *
     * @param Credentials $credentials
     * @return self
     */
    public function loadByCredentials(Credentials $credentials): self
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('username', $credentials->getUsername())
            ->addFieldToFilter('environment', $credentials->getEnvironment());

        if (count($collection) > 0) {
            $this->load($collection->getLastItem()->getId());
        }

        return $this;
    }
}
