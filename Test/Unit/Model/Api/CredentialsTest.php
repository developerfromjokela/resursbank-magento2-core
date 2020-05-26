<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model\Api;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Model\Api\Credentials;

/**
 * Test cases designed for Credentials data model.
 *
 * @package Resursbank\Core\Test\Unit\Model\Api
 */
class CredentialsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Credentials
     */
    private $credentials;

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
        $this->credentials = $this->objectManager
            ->getObject(Credentials::class);
        $this->store = $this->objectManager->getObject(Store::class);
    }

    /**
     * Assert that we get an instance of ValidatorException when we attempt to
     * assign an empty string as username value on the Credentials model
     * instance.
     *
     * @return void
     */
    public function testValidatorExceptionThrownWithEmptyUsername(): void
    {
        $this->expectException(ValidatorException::class);

        $this->credentials->setUsername('');
    }

    /**
     * Assert that we get an instance of ValidatorException when we attempt to
     * assign an empty string as password value on the Credentials model
     * instance.
     *
     * @return void
     */
    public function testValidatorExceptionThrownWithEmptyPassword(): void
    {
        $this->expectException(ValidatorException::class);

        $this->credentials->setPassword('');
    }

    /**
     * Assert that "0" is a valid environment value.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testCanSetEnvironment0(): void
    {
        static::assertInstanceOf(
            Credentials::class,
            $this->credentials->setEnvironment(0)
        );
    }

    /**
     * Assert that "1" is a valid environment value.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testCanSetEnvironment1(): void
    {
        static::assertInstanceOf(
            Credentials::class,
            $this->credentials->setEnvironment(1)
        );
    }

    /**
     * Assert that applying an environment value of less than "0" results in an
     * instance of ValidatorException.
     *
     * @return void
     */
    public function testValidatorExceptionThrownWithEnvironmentBelow0(): void
    {
        $this->expectException(ValidatorException::class);

        $this->credentials->setEnvironment(-1);
    }

    /**
     * Assert that applying an environment value of more than "1" results in an
     * instance of ValidatorException.
     *
     * @return void
     */
    public function testValidatorExceptionThrownWithEnvironmentAbove1(): void
    {
        $this->expectException(ValidatorException::class);

        $this->credentials->setEnvironment(2);
    }

    /**
     * Assert the store setter works.
     *
     * @return void
     */
    public function testSetStore(): void
    {
        $this->credentials->setStore($this->store);

        static::assertInstanceOf(Store::class, $this->credentials->getStore());
    }

    /**
     * Assert the store getter works.
     *
     * @return void
     */
    public function testGetStoreReturns(): void
    {
        static::assertNull($this->credentials->getStore());

        $this->credentials->setStore($this->store);

        static::assertInstanceOf(Store::class, $this->credentials->getStore());
    }
}
