<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper;

use Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Helper\Api\Credentials as CredentialsHelper;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Helper\Version;
use Resursbank\Core\Model\Api\Credentials as CredentialsModel;
use InvalidArgumentException;

class ApiTest extends TestCase
{
    /**
     * @var Api
     */
    private Api $api;

    /**
     * @var MockObject|Version
     */
    private $versionHelperMock;

    /**
     * @var MockObject|CredentialsModel
     */
    private $credentialsModelMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = ObjectManager::getInstance();
        $contextMock = $this->createMock(Context::class);
        $storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class
        );
        $orderHelperMock = $this->createMock(Order::class);
        $this->versionHelperMock = $this->createMock(Version::class);
        $this->credentialsModelMock = $this->createMock(
            CredentialsModel::class
        );
        $resursConfigMock = $this->createMock(Config::class);

        $credentialsHelper = new CredentialsHelper(
            $contextMock,
            $resursConfigMock,
            $objectManager,
            $storeManagerMock
        );

        $this->api = new Api(
            $contextMock,
            $credentialsHelper,
            $orderHelperMock,
            $this->versionHelperMock
        );

        parent::setUp();
    }

    /**
     * Assert that getUserAgent returns the correct value when specific version
     * is supplied from version helper.
     *
     * @return void
     */
    public function testGetUserAgentReturnsCorrectValue(): void
    {
        /** @phpstan-ignore-next-line Undefined method */
        $this->versionHelperMock->method('getComposerVersion')
            ->willReturn('1.0.0');

        self::assertSame(
            $this->api->getUserAgent(),
            'Magento 2 | Resursbank_Core 1.0.0'
        );
    }

    /**
     * Assert that getConnection function throws error if password is missing.
     *
     * @throws Exception
     */
    public function testGetConnectionThrowsExceptionWithMissingPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage(
            'Failed to establish API connection, incomplete Credentials.'
        );
        /** @phpstan-ignore-next-line Undefined method */
        $this->credentialsModelMock->method('getUsername')
            ->willReturn('username');
        /** @phpstan-ignore-next-line Undefined method */
        $this->credentialsModelMock->method('getPassword')->willReturn(null);
        /** @phpstan-ignore-next-line Undefined method */
        $this->credentialsModelMock->method('getEnvironment')->willReturn(1);
        /** @phpstan-ignore-next-line Wrong parameter type. */
        $this->api->getConnection($this->credentialsModelMock);
    }

    /**
     * Assert that getConnection function throws error if username is missing.
     *
     * @throws Exception
     */
    public function testGetConnectionThrowsExceptionWithMissingUsername(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage(
            'Failed to establish API connection, incomplete Credentials.'
        );
        /** @phpstan-ignore-next-line Undefined method */
        $this->credentialsModelMock->method('getUsername')->willReturn(null);
        /** @phpstan-ignore-next-line Undefined method */
        $this->credentialsModelMock->method('getPassword')
            ->willReturn('password');
        /** @phpstan-ignore-next-line Undefined method */
        $this->credentialsModelMock->method('getEnvironment')->willReturn(1);
        /** @phpstan-ignore-next-line Wrong parameter type. */
        $this->api->getConnection($this->credentialsModelMock);
    }

    /**
     * Assert that getConnection function throws error if environment is missing.
     *
     * @throws Exception
     */
    public function testGetConnectionThrowsExceptionWithMissingEnvironment(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage(
            'Failed to establish API connection, incomplete Credentials.'
        );
        /** @phpstan-ignore-next-line Undefined method */
        $this->credentialsModelMock->method('getUsername')
            ->willReturn('username');
        /** @phpstan-ignore-next-line Undefined method */
        $this->credentialsModelMock->method('getPassword')
            ->willReturn('password');
        /** @phpstan-ignore-next-line Undefined method */
        $this->credentialsModelMock->method('getEnvironment')->willReturn(null);
        /** @phpstan-ignore-next-line Wrong parameter type. */
        $this->api->getConnection($this->credentialsModelMock);
    }
}
