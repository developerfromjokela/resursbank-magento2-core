<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\User;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Admin;

/**
 * Test cases designed for Resursbank\Core\Helper\Admin
 */
class AdminTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Session
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
            ->addMethods(['getUser'])
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

        /** @phpstan-ignore-next-line */
        static::assertEquals('Anonymous', $admin->getUserName());
    }

    /**
     * Assert that the getUser method will return the corresponding username
     * when there is a user in our session.
     *
     * @return void
     */
    public function testUsernameWorksWithUser(): void
    {
        // Mock a User object.
        $user = $this->objectManager->getObject(User::class);

        /** @phpstan-ignore-next-line */
        $user->setUserName('Lebowski');

        // Modify the output of getUser method in Session mock.
        /** @phpstan-ignore-next-line */
        $this->session->method('getUser')->willReturn($user);

        // Create a new Admin instance using our mocked Session.
        $admin = $this->objectManager
            ->getObject(Admin::class, ['session' => $this->session]);

        /** @phpstan-ignore-next-line */
        static::assertEquals('Lebowski', $admin->getUserName());
    }
}
