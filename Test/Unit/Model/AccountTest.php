<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Model\Account;

/**
 * Test cases designed for Account data model.
 *
 * @package Resursbank\Core\Test\Unit\Model
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
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
     * Assert that the setAccountId method will assign a value to the accountId
     * property.
     *
     * @return void
     */
    public function testSetAccountId(): void
    {
        static::assertNull(
            $this->account->getData(Account::ACCOUNT_ID)
        );

        $this->account->setAccountId(44);

        static::assertSame(
            44,
            $this->account->getData(Account::ACCOUNT_ID)
        );
    }

    /**
     * Assert that the method setAccountId will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetAccountIdReturnSelf(): void
    {
        static::assertInstanceOf(
            Account::class,
            $this->account->setAccountId(6)
        );
    }

    /**
     * Assert that the method getAccountId will convert its return value to an
     * int.
     *
     * @return void
     */
    public function testGetAccountIdTypeConversionReturn(): void
    {
        $this->account->setData(
            Account::ACCOUNT_ID,
            ['robo', 'default', 'test']
        );

        static::assertSame(1, $this->account->getAccountId());
    }

    /**
     * Assert that the getAccountId method will return default value when no
     * value has been assigned to the accountId property.
     *
     * @return void
     */
    public function testGetAccountIdDefaultReturn(): void
    {
        $this->account->setData(Account::ACCOUNT_ID, null);
        static::assertSame(9999, $this->account->getAccountId(9999));
        static::assertNull($this->account->getAccountId());
    }

    /**
     * Assert that the getAccountId method will return the value assigned to the
     * accountId property.
     *
     * @return void
     */
    public function testGetAccountIdExpectedReturn(): void
    {
        $this->account->setData(Account::ACCOUNT_ID, 672);
        static::assertSame(672, $this->account->getAccountId(55));
        static::assertSame(672, $this->account->getAccountId());
    }

    /**
     * Assert that the setUsername method will assign a value to the username
     * property.
     *
     * @return void
     */
    public function testSetUsername(): void
    {
        static::assertNull(
            $this->account->getData(Account::USERNAME)
        );

        try {
            $this->account->setUsername('Crono');
        } catch (ValidatorException $e) {
            static::fail('Could not set username "Crono"');
        }

        static::assertSame(
            'Crono',
            $this->account->getData(Account::USERNAME)
        );
    }

    /**
     * Assert that the method setUsername will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetUsernameReturnSelf(): void
    {
        try {
            static::assertInstanceOf(
                Account::class,
                $this->account->setUsername('Sylvando')
            );
        } catch (ValidatorException $e) {
            static::fail('Could not set username "Sylvando"');
        }
    }

    /**
     * Assert that an instance of ValidatorException exception is thrown if
     * the setUsername method is provided an empty string.
     *
     * @return void
     */
    public function testSetUsernameThrowsOnEmptyUsername(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Username cannot be empty.');
        $this->account->setUsername('');
    }

    /**
     * Assert that the method getUsername will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testGetUsernameTypeConversionReturn(): void
    {
        $this->account->setData(Account::USERNAME, 55.1);
        static::assertSame('55.1', $this->account->getUsername());
    }

    /**
     * Assert that the getUsername method will return default value when no
     * value has been assigned to the username property.
     *
     * @return void
     */
    public function testGetUsernameDefaultReturn(): void
    {
        $this->account->setData(Account::USERNAME, null);
        static::assertSame('Skywalker', $this->account->getUsername('Skywalker'));
        static::assertNull($this->account->getUsername());
    }

    /**
     * Assert that the getUsername method will return the value assigned to the
     * username property.
     *
     * @return void
     */
    public function testGetUsernameExpectedReturn(): void
    {
        $this->account->setData(Account::USERNAME, 'Sora');
        static::assertSame('Sora', $this->account->getUsername('Sonic'));
        static::assertSame('Sora', $this->account->getUsername());
    }

    /**
     * Assert that the setIsTest method will assign a value to the
     * environment property.
     *
     * @return void
     */
    public function testSetIsTest(): void
    {
        static::assertNull(
            $this->account->getData(Account::IS_TEST)
        );

        $this->account->setIsTest(false);

        static::assertFalse(
            $this->account->getData(Account::IS_TEST)
        );
    }

    /**
     * Assert that the method setIsTest will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetIsTestReturnSelf(): void
    {
        static::assertInstanceOf(
            Account::class,
            $this->account->setIsTest(true)
        );
    }

    /**
     * Assert that the method getIsTest will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testGetIsTestTypeConversionReturn(): void
    {
        $this->account->setData(Account::IS_TEST, 1);
        static::assertTrue($this->account->getIsTest());
    }

    /**
     * Assert that the getIsTest method will return default value when no
     * value has been assigned to the environment property.
     *
     * @return void
     */
    public function testGetIsTestDefaultReturn(): void
    {
        $this->account->setData(Account::IS_TEST, null);
        static::assertTrue($this->account->getIsTest(true));
        static::assertNull($this->account->getIsTest());
    }

    /**
     * Assert that the getIsTest method will return the value assigned to
     * the environment property.
     *
     * @return void
     */
    public function testGetIsTestExpectedReturn(): void
    {
        $this->account->setData(Account::IS_TEST, true);
        static::assertTrue($this->account->getIsTest(false));
        static::assertTrue($this->account->getIsTest());
    }

    /**
     * Assert that the setSalt method will assign a value to the salt property.
     *
     * @return void
     */
    public function testSetSalt(): void
    {
        static::assertNull(
            $this->account->getData(Account::SALT)
        );

        $this->account->setSalt('Df5erfg4rfgDFGDFgfghcvdfgh4345wdfWDwrt2fedfg');

        static::assertSame(
            'Df5erfg4rfgDFGDFgfghcvdfgh4345wdfWDwrt2fedfg',
            $this->account->getData(Account::SALT)
        );
    }

    /**
     * Assert that the method setSalt will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetSaltReturnSelf(): void
    {
        static::assertInstanceOf(
            Account::class,
            $this->account->setSalt('Df445th5hRFg45thrgh44rgf4grghy4Fhrfh343r')
        );
    }

    /**
     * Assert that the method getSalt will convert its return value to a string.
     *
     * @return void
     */
    public function testGetSaltTypeConversionReturn(): void
    {
        $this->account->setData(Account::SALT, 4455677);
        static::assertSame('4455677', $this->account->getSalt());
    }

    /**
     * Assert that the getSalt method will return default value when no value
     * has been assigned to the salt property.
     *
     * @return void
     */
    public function testGetSaltDefaultReturn(): void
    {
        $this->account->setData(Account::SALT, null);
        static::assertSame(
            'fgdhh5yhrh567Rhrh45r3gf',
            $this->account->getSalt('fgdhh5yhrh567Rhrh45r3gf')
        );
        static::assertNull($this->account->getSalt());
    }

    /**
     * Assert that the getSalt method will return the value assigned to the salt
     * property.
     *
     * @return void
     */
    public function testGetSaltExpectedReturn(): void
    {
        $this->account->setData(Account::SALT, 'mnFHg4rtKLoa45623D');
        static::assertSame(
            'mnFHg4rtKLoa45623D',
            $this->account->getSalt('76FHh4rhr')
        );
        static::assertSame('mnFHg4rtKLoa45623D', $this->account->getSalt());
    }

    /**
     * Assert that the setCreatedAt method will assign a value to the createdAt
     * property.
     *
     * @return void
     */
    public function testSetCreatedAt(): void
    {
        $time = (string) time();

        static::assertNull(
            $this->account->getData(Account::CREATED_AT)
        );

        try {
            $this->account->setCreatedAt($time);
        } catch (ValidatorException $e) {
            static::fail('Could not set created_at timestamp "' . $time . '"');
        }

        static::assertSame(
            $time,
            $this->account->getData(Account::CREATED_AT)
        );
    }

    /**
     * Assert that the method setCreatedAt will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetCreatedAtReturnSelf(): void
    {
        $time = (string) time();

        try {
            static::assertInstanceOf(
                Account::class,
                $this->account->setCreatedAt($time)
            );
        } catch (ValidatorException $e) {
            static::fail('Could not set created_at timestamp "' . $time . '"');
        }
    }

    /**
     * Assert that the setCreatedAt method throws an instance of
     * ValidatorException if a none numeric value is provided.
     *
     * @return void
     */
    public function testSetCreatedAtThrowsOnNoNNumericValue(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Created at must be numeric.');
        $this->account->setCreatedAt('Not a numeric value');
    }

    /**
     * Assert that the method getCreatedAt will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testGetCreatedAtTypeConversionReturn(): void
    {
        $this->account->setData(Account::CREATED_AT, 87);
        static::assertSame('87', $this->account->getCreatedAt());
    }

    /**
     * Assert that the getCreatedAt method will return default value when no
     * value has been assigned to the createdAt property.
     *
     * @return void
     */
    public function testGetCreatedAtDefaultReturn(): void
    {
        $this->account->setData(Account::CREATED_AT, null);
        static::assertSame('1123543', $this->account->getCreatedAt('1123543'));
        static::assertNull($this->account->getCreatedAt());
    }

    /**
     * Assert that the getCreatedAt method will return the value assigned to the
     * createdAt property.
     *
     * @return void
     */
    public function testGetCreatedAtExpectedReturn(): void
    {
        $this->account->setData(Account::CREATED_AT, '989898');
        static::assertSame('989898', $this->account->getCreatedAt('555'));
        static::assertSame('989898', $this->account->getCreatedAt());
    }

    /**
     * Assert that the setUpdatedAt method will assign a value to the updatedAt
     * property.
     *
     * @return void
     */
    public function testSetUpdatedAt(): void
    {
        $time = (string) time();

        static::assertNull(
            $this->account->getData(Account::UPDATED_AT)
        );

        try {
            $this->account->setUpdatedAt($time);
        } catch (ValidatorException $e) {
            static::fail('Could not set update_at timestamp "' . $time . '"');
        }

        static::assertSame(
            $time,
            $this->account->getData(Account::UPDATED_AT)
        );
    }

    /**
     * Assert that the method setUpdatedAt will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetUpdatedAtReturnSelf(): void
    {
        $time = (string) time();

        try {
            static::assertInstanceOf(
                Account::class,
                $this->account->setUpdatedAt($time)
            );
        } catch (ValidatorException $e) {
            static::fail('Could not set update_at timestamp "' . $time . '"');
        }
    }

    /**
     * Assert that the setUpdatedAt method throws an instance of
     * ValidatorException if a none numeric value is provided.
     *
     * @return void
     */
    public function testSetUpdatedAtThrowsOnNoNNumericValue(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Updated at must be numeric.');
        $this->account->setUpdatedAt('Not a numeric value');
    }

    /**
     * Assert that the method getUpdatedAt will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testGetUpdatedAtTypeConversionReturn(): void
    {
        $this->account->setData(Account::UPDATED_AT, 1234.23);
        static::assertSame('1234.23', $this->account->getUpdatedAt());
    }

    /**
     * Assert that the getUpdatedAt method will return default value when no
     * value has been assigned to the updatedAt property.
     *
     * @return void
     */
    public function testGetUpdatedAtDefaultReturn(): void
    {
        $this->account->setData(Account::UPDATED_AT, null);
        static::assertSame(
            'This is my default',
            $this->account->getUpdatedAt('This is my default')
        );
        static::assertNull($this->account->getUpdatedAt());
    }

    /**
     * Assert that the getUpdatedAt method will return the value assigned to the
     * updatedAt property.
     *
     * @return void
     */
    public function testGetUpdatedAtExpectedReturn(): void
    {
        $this->account->setData(Account::UPDATED_AT, 'Expected');
        static::assertSame('Expected', $this->account->getUpdatedAt('Def'));
        static::assertSame('Expected', $this->account->getUpdatedAt());
    }
}
