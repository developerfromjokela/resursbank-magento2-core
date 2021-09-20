<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Helper\Version;
use InvalidArgumentException;

/**
 * Test cases designed for the Api service.
 */
class ApiTest extends TestCase
{

    /**
     * @var MockObject|Api
     */
    private $api;

    /**
     * @var MockObject|Version
     */
    private $versionHelperMock;

    /**
     * @var MockObject|\Resursbank\Core\Model\Api\Credentials
     */
    private $credentialsModelMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = ObjectManager::getInstance();
        $contextMock = $this->createMock(Context::class);
        $StoreManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $orderHelperMock = $this->createMock(Order::class);
        $this->versionHelperMock = $this->createMock(Version::class);
        $this->credentialsModelMock = $this->createMock(\Resursbank\Core\Model\Api\Credentials::class);
        $resursConfigMock = $this->createMock(Config::class);

        $credentialsHelper = new Credentials(
            $contextMock,
            $resursConfigMock,
            $objectManager,
            $StoreManagerMock
        );

        $this->api = new Api(
            $contextMock,
            $credentialsHelper,
            $orderHelperMock,
            $this->versionHelperMock
        );
    }

    /**
     * Assert that getUserAgent returns the correct value when specific version is supplied from versionhelper
     *
     * @return void
     */
    public function testGetUserAgentReturnsCorrectValue(): void
    {
        $this->versionHelperMock->method('getComposerVersion')->willReturn("1.0.0");
        self::assertSame($this->api->getUserAgent(), "Magento 2 | Resursbank_Core 1.0.0");
    }

    /**
     * Assert that getConnection function throws error if password is missing
     *
     * @throws \Exception
     */
    public function testGetConnectionThrowsExceptionWithMissingPassword()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage("Failed to establish API connection, incomplete Credentials.");
        $this->credentialsModelMock->method("getUsername")->willReturn("username");
        $this->credentialsModelMock->method("getPassword")->willReturn(null);
        $this->credentialsModelMock->method("getEnvironment")->willReturn(1);
        $this->api->getConnection($this->credentialsModelMock);
    }

    /**
     * Assert that getConnection function throws error if username is missing
     *
     * @throws \Exception
     */
    public function testGetConnectionThrowsExceptionWithMissingUsername()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage("Failed to establish API connection, incomplete Credentials.");
        $this->credentialsModelMock->method("getUsername")->willReturn(null);
        $this->credentialsModelMock->method("getPassword")->willReturn("password");
        $this->credentialsModelMock->method("getEnvironment")->willReturn(1);
        $this->api->getConnection($this->credentialsModelMock);
    }

    /**
     * Assert that getConnection function throws error if environment is missing
     *
     * @throws \Exception
     */
    public function testGetConnectionThrowsExceptionWithMissingEnvironment()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage("Failed to establish API connection, incomplete Credentials.");
        $this->credentialsModelMock->method("getUsername")->willReturn("username");
        $this->credentialsModelMock->method("getPassword")->willReturn("password");
        $this->credentialsModelMock->method("getEnvironment")->willReturn(null);
        $this->api->getConnection($this->credentialsModelMock);
    }
}
