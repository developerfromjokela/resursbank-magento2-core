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
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Admin;

/**
 * Test cases designed for Resursbank\Core\Helper\Admin
 *
 * @package Resursbank\Core\Test\Model\Config\Source
 */
class AdminTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject
     */
    private $session;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Mock the Session class and whitelist the getUser method so we can
        // modify its output.
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();
    }

    /**
     * Assert that the getUser method will return "Anonymous" if there is no
     * user in the session.
     *
     * @return void
     */
    public function testUsernameWorksWithoutUser(): void
    {
        // Mock Admin without any User in Session.
        $admin = $this->objectManager
            ->getObject(Admin::class, ['session' => $this->session]);

        self::assertEquals('Anonymous', $admin->getUsername());
    }

    /**
     * Assert that the getUser method will return the name of the user in
     * the session.
     *
     * @return void
     */
    public function testUsernameWorksWithUser(): void
    {
        // Mock a User object.
        $user = $this->objectManager->getObject(User::class);
        $user->setUserName('Lebowski');

        // Modify the output of getUser method on Session mock.
        $this->session->expects(self::any())
            ->method('getUser')
            ->will(self::returnValue($user));

        // Create a new Admin instance using our mocked Session.
        $admin = $this->objectManager
            ->getObject(Admin::class, ['session' => $this->session]);

        self::assertEquals('Lebowski', $admin->getUsername());
    }
}
