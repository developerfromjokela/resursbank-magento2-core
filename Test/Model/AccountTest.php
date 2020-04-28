<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Model;

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
     * Assert the value resulting from the getAccountId method matches the value
     * supplied to the setAccountId method.
     */
    public function testAccountId()
    {
        $this->account->setAccountId(5);

        self::assertSame(5, $this->account->getAccountId());
    }

    /**
     * Assert the value resulting from the getUsername method matches the value
     * supplied to the setUsername method.
     *
     * @throws ValidatorException
     */
    public function testUsername()
    {
        $this->account->setUsername('Jeff');

        self::assertSame('Jeff', $this->account->getUsername());
    }

    /**
     * Assert the setUsername methods throws a ValidatorException when provided
     * and empty value.
     *
     * @throws ValidatorException
     */
    public function testSetUsernameThrowsExceptionWithEmptyValue()
    {
        $this->expectException(ValidatorException::class);

        $this->account->setUsername('');
    }

    /**
     * Assert the value resulting from the getEnvironment method matches the
     * value supplied to the setEnvironment method.
     */
    public function testEnvironment()
    {
        $this->account->setEnvironment('prod');

        self::assertSame('prod', $this->account->getEnvironment());
    }

    /**
     * Assert the value resulting from the getSalt method matches the
     * value supplied to the setSalt method.
     */
    public function testSalt()
    {
        $this->account->setSalt('dfgdg456y4rtgrhrth456DFg34tefgH6edfg2DF4');

        self::assertSame(
            'dfgdg456y4rtgrhrth456DFg34tefgH6edfg2DF4',
            $this->account->getSalt()
        );
    }

    /**
     * Assert the value resulting from the getCreatedAt method matches the
     * value supplied to the setCreatedAt method.
     *
     * @throws ValidatorException
     */
    public function testCreatedAt()
    {
        $time = (string) time();

        $this->account->setCreatedAt($time);

        self::assertSame($time, $this->account->getCreatedAt());
    }

    /**
     * Assert that the method setCreatedAt will throw an instance of
     * ValidatorException if provided a value which isn't numeric.
     */
    public function testCreatedAtMustBeNumeric()
    {
        $this->expectException(ValidatorException::class);

        $this->account->setCreatedAt('testing');
    }

    /**
     * Assert the value resulting from the getUpdatedAt method matches the
     * value supplied to the setUpdatedAt method.
     *
     * @throws ValidatorException
     */
    public function testUpdatedAt()
    {
        $time = (string) time();

        $this->account->setUpdatedAt($time);

        self::assertSame($time, $this->account->getUpdatedAt());
    }

    /**
     * Assert that the method setUpdatedAt will throw an instance of
     * ValidatorException if provided a value which isn't numeric.
     */
    public function testUpdatedAtMustBeNumeric()
    {
        $this->expectException(ValidatorException::class);

        $this->account->setUpdatedAt('womba');
    }
}
