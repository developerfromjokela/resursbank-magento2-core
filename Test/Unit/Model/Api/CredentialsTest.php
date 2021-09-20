<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model\Api;

use Magento\Framework\Exception\ValidatorException;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\RBEcomPHP\ResursBank;

/**
 * Test cases designed for Credentials data model.
 */
class CredentialsTest extends TestCase
{
    /**
     * @var Credentials
     */
    private Credentials $credentials;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->credentials = new Credentials();
    }


    /**
     * Assert that the username method will assign a value to the username prop.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testSetUsername(): void
    {
        $this->credentials->setUsername('lorem');

        self::assertSame('lorem', $this->credentials->getUsername());
    }

    /**
     * Assert that the password method will assign a value to the password prop.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testSetPassword(): void
    {
        $this->credentials->setPassword('ipsum');

        self::assertSame('ipsum', $this->credentials->getPassword());
    }

    /**
     * Assert that the password method will assign a value to the password prop.
     *
     * @return void
     */
    public function testSetCountry(): void
    {
        $this->credentials->setCountry('SE');

        self::assertSame('SE', $this->credentials->getCountry());
    }

    /**
     * Assert that the password method will assign a value to the password prop.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testSetEnvironment(): void
    {
        $this->credentials->setEnvironment(ResursBank::ENVIRONMENT_PRODUCTION);

        self::assertSame(ResursBank::ENVIRONMENT_PRODUCTION, $this->credentials->getEnvironment());
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
     * Assert the country getter works.
     *
     * @return void
     */
    public function testGetCountryReturns(): void
    {
        static::assertNull($this->credentials->getCountry());

        $this->credentials->setCountry('NO');

        static::assertSame('NO', $this->credentials->getCountry());
    }
}
