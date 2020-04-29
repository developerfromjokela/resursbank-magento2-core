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

declare(strict_types=1);

namespace Resursbank\Core\Test\Helper;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\User;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Admin;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test cases designed for Resursbank\Core\Helper\Admin
 *
 * @package Resursbank\Core\Test\Helper
 */
class AdminTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Store
     */
    private $store;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->store = $this->createMock(Store::class);
    }

    public function testNosuchEntityExceptionWithoutStore()
    {
         $this->expectException(NoSuchEntityException::class);

         $this->storeManager->getStore();
    }

}
