<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Resursbank\Core\Model\Account;
use PHPUnit\Framework\TestCase;

/**
 * Test cases designed for Account data model.
 *
 * @package Resursbank\Core\Test\Model
 */
class AccountTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Account
     */
    private $account;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->account = $this->objectManager->getObject(Account::class);
    }

    /**
     * Assert that we can set an account id.
     */
    public function testSetAccountId(): void
    {
        self::assertNull(
            $this->account->getData(Account::ACCOUNT_ID)
        );

        $this->account->setAccountId(123);

        self::assertSame(
            123,
            $this->account->getData(Account::ACCOUNT_ID)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetAccountIdReturnSelf(): void
    {
        self::assertInstanceOf(
            Account::class,
            $this->account->setAccountId(123)
        );
    }

    /**
     * Assert that the return value is converted to an int.
     */
    public function testGetAccountIdTypeConversionReturn(): void
    {
        $this->account->setData(Account::ACCOUNT_ID, 'Test');
        self::assertSame(0, $this->account->getAccountId());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testGetAccountIdDefaultReturn(): void
    {
        $this->account->setData(Account::ACCOUNT_ID, null);
        self::assertSame(321, $this->account->getAccountId(321));
        self::assertNull($this->account->getAccountId());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testGetAccountIdExpectedReturn(): void
    {
        $this->account->setData(Account::ACCOUNT_ID, 123);
        self::assertSame(123, $this->account->getAccountId(321));
        self::assertSame(123, $this->account->getAccountId());
    }

    /**
     * Assert that we can set a username.
     */
    public function testSetUsername(): void
    {
        self::assertNull(
            $this->account->getData(Account::USERNAME)
        );

        try {
            $this->account->setUsername('Test');
        } catch (ValidatorException $e) {
            self::fail('Could not set username "Test"');
        }

        self::assertSame(
            'Test',
            $this->account->getData(Account::USERNAME)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetUsernameReturnSelf(): void
    {
        try {
            self::assertInstanceOf(
                Account::class,
                $this->account->setUsername('Test')
            );
        } catch (ValidatorException $e) {
            self::fail('Could not set username "Test"');
        }
    }

    /**
     * Assert that an exception is thrown if an empty username is given.
     */
    public function testSetUsernameThrowsOnEmptyUsername(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Username cannot be empty.');
        $this->account->setUsername('');
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testGetUsernameTypeConversionReturn(): void
    {
        $this->account->setData(Account::USERNAME, 123);
        self::assertSame('123', $this->account->getUsername());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testGetUsernameDefaultReturn(): void
    {
        $this->account->setData(Account::USERNAME, null);
        self::assertSame('321', $this->account->getUsername('321'));
        self::assertNull($this->account->getUsername());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testGetUsernameExpectedReturn(): void
    {
        $this->account->setData(Account::USERNAME, '123');
        self::assertSame('123', $this->account->getUsername('321'));
        self::assertSame('123', $this->account->getUsername());
    }

    /**
     * Assert that we can set the environment.
     */
    public function testSetEnvironment(): void
    {
        self::assertNull(
            $this->account->getData(Account::ENVIRONMENT)
        );

        $this->account->setEnvironment('Test');

        self::assertSame(
            'Test',
            $this->account->getData(Account::ENVIRONMENT)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetEnvironmentReturnSelf(): void
    {
        self::assertInstanceOf(
            Account::class,
            $this->account->setEnvironment('Test')
        );
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testGetEnvironmentTypeConversionReturn(): void
    {
        $this->account->setData(Account::ENVIRONMENT, 123);
        self::assertSame('123', $this->account->getEnvironment());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testGetEnvironmentDefaultReturn(): void
    {
        $this->account->setData(Account::ENVIRONMENT, null);
        self::assertSame('321', $this->account->getEnvironment('321'));
        self::assertNull($this->account->getEnvironment());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testGetEnvironmentExpectedReturn(): void
    {
        $this->account->setData(Account::ENVIRONMENT, '123');
        self::assertSame('123', $this->account->getEnvironment('321'));
        self::assertSame('123', $this->account->getEnvironment());
    }

    /**
     * Assert that we can set a salt.
     */
    public function testSetSalt(): void
    {
        self::assertNull(
            $this->account->getData(Account::SALT)
        );

        $this->account->setSalt('Test');

        self::assertSame(
            'Test',
            $this->account->getData(Account::SALT)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetSaltReturnSelf(): void
    {
        self::assertInstanceOf(
            Account::class,
            $this->account->setSalt('Test')
        );
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testGetSaltTypeConversionReturn(): void
    {
        $this->account->setData(Account::SALT, 123);
        self::assertSame('123', $this->account->getSalt());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testGetSaltDefaultReturn(): void
    {
        $this->account->setData(Account::SALT, null);
        self::assertSame('321', $this->account->getSalt('321'));
        self::assertNull($this->account->getSalt());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testGetSaltExpectedReturn(): void
    {
        $this->account->setData(Account::SALT, '123');
        self::assertSame('123', $this->account->getSalt('321'));
        self::assertSame('123', $this->account->getSalt());
    }

    /**
     * Assert that we can set a timestamp.
     */
    public function testSetCreatedAt(): void
    {
        $time = (string) time();

        self::assertNull(
            $this->account->getData(Account::CREATED_AT)
        );

        try {
            $this->account->setCreatedAt($time);
        } catch (ValidatorException $e) {
            self::fail('Could not set created_at timestamp "' . $time . '"');
        }

        self::assertSame(
            $time,
            $this->account->getData(Account::CREATED_AT)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetCreatedAtReturnSelf(): void
    {
        $time = (string) time();

        try {
            self::assertInstanceOf(
                Account::class,
                $this->account->setCreatedAt($time)
            );
        } catch (ValidatorException $e) {
            self::fail('Could not set created_at timestamp "' . $time . '"');
        }
    }

    /**
     * Assert that an exception is thrown if a faulty timestamp is given.
     */
    public function testSetCreatedAtThrowsOnNoNNumericValue(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Created at must be numeric.');
        $this->account->setCreatedAt('Not a numeric value');
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testGetCreatedAtTypeConversionReturn(): void
    {
        $this->account->setData(Account::CREATED_AT, 123);
        self::assertSame('123', $this->account->getCreatedAt());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testGetCreatedAtDefaultReturn(): void
    {
        $this->account->setData(Account::CREATED_AT, null);
        self::assertSame('321', $this->account->getCreatedAt('321'));
        self::assertNull($this->account->getCreatedAt());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testGetCreatedAtExpectedReturn(): void
    {
        $this->account->setData(Account::CREATED_AT, '123');
        self::assertSame('123', $this->account->getCreatedAt('321'));
        self::assertSame('123', $this->account->getCreatedAt());
    }

    /**
     * Assert that we can set a timestamp.
     */
    public function testSetUpdatedAt(): void
    {
        $time = (string) time();

        self::assertNull(
            $this->account->getData(Account::UPDATED_AT)
        );

        try {
            $this->account->setUpdatedAt($time);
        } catch (ValidatorException $e) {
            self::fail('Could not set update_at timestamp "' . $time . '"');
        }

        self::assertSame(
            $time,
            $this->account->getData(Account::UPDATED_AT)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetUpdatedAtReturnSelf(): void
    {
        $time = (string) time();

        try {
            self::assertInstanceOf(
                Account::class,
                $this->account->setUpdatedAt($time)
            );
        } catch (ValidatorException $e) {
            self::fail('Could not set update_at timestamp "' . $time . '"');
        }
    }

    /**
     * Assert that an exception is thrown if a faulty timestamp is given.
     */
    public function testSetUpdatedAtThrowsOnNoNNumericValue(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Updated at must be numeric.');
        $this->account->setUpdatedAt('Not a numeric value');
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testGetUpdatedAtTypeConversionReturn(): void
    {
        $this->account->setData(Account::UPDATED_AT, 123);
        self::assertSame('123', $this->account->getUpdatedAt());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testGetUpdatedAtDefaultReturn(): void
    {
        $this->account->setData(Account::UPDATED_AT, null);
        self::assertSame('321', $this->account->getUpdatedAt('321'));
        self::assertNull($this->account->getUpdatedAt());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testGetUpdatedAtExpectedReturn(): void
    {
        $this->account->setData(Account::UPDATED_AT, '123');
        self::assertSame('123', $this->account->getUpdatedAt('321'));
        self::assertSame('123', $this->account->getUpdatedAt());
    }
}
