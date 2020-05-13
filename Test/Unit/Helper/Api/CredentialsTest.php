<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper\Api;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Exception\MissingDataException;
use Resursbank\Core\Model\Api\Credentials as Model;
use Resursbank\Core\Helper\Api\Credentials as Helper;

/**
 * Test cases designed for Credentials data model.
 *
 * @package Resursbank\Core\Test\Unit\Helper\Api
 */
class CredentialsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager
            ->getObject(Model::class);
        $this->helper = $this->objectManager
            ->getObject(Helper::class);
    }

    /**
     * Assert that hasCredentials method will result in "true" if a username and
     * password value have been applied on the Credentials model instance.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testHasCredentialsTrueWithUsernameAndPassword(): void
    {
        $this->model
            ->setUsername('testing')
            ->setPassword('secret');

        self::assertTrue($this->helper->hasCredentials($this->model));
    }

    /**
     * Assert that hasCredentials method will result in "false" if no username
     * value has been applied on the Credentials model instance.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testHasCredentialsFalseWithoutUsername(): void
    {
        $this->model->setPassword('secret');

        self::assertFalse($this->helper->hasCredentials($this->model));
    }

    /**
     * Assert that hasCredentials method will result in "false" if no
     * password value has been applied on the Credentials model instance.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testHasCredentialsFalseWithoutPassword(): void
    {
        $this->model->setUsername('lebowski');

        self::assertFalse($this->helper->hasCredentials($this->model));
    }

    /**
     * Assert that attempting to generate a hash without a username results in
     * an instance of MissingDataException.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testExceptionThrownWhenGeneratingHashWithoutUsername(): void
    {
        $this->expectException(MissingDataException::class);

        $this->model->setEnvironment(1);

        $this->helper->getHash($this->model);
    }

    /**
     * Assert that attempting to generate a hash without an environment results
     * in an instance of MissingDataException.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testExceptionThrownWhenGeneratingHashWithoutEnv(): void
    {
        $this->expectException(MissingDataException::class);

        $this->model->setUsername('testing');

        $this->helper->getHash($this->model);
    }

    /**
     * Assert that the getHash method returns the expected result when username
     * and environment are applied on the supplied Credentials model instance.
     *
     * @return void
     * @throws MissingDataException
     * @throws ValidatorException
     */
    public function testHashValue(): void
    {
        $this->model
            ->setUsername('testaccount')
            ->setEnvironment(1);

        self::assertSame(
            'a8c850514b63b1c6513ddd19599e9235c93ccd0b',
            $this->helper->getHash($this->model)
        );
    }

    /**
     * Assert that the getEnvironmentCode method will result in
     * Helper::ENVIRONMENT_CODE_TEST with environment "1" applied.
     *
     * @return void
     * @throws MissingDataException
     * @throws ValidatorException
     */
    public function testGetEnvironmentCodeForTest(): void
    {
        $this->model->setEnvironment(1);

        self::assertSame(
            Helper::ENVIRONMENT_CODE_TEST,
            $this->helper->getEnvironmentCode($this->model)
        );
    }

    /**
     * Assert that the getEnvironmentCode method will result in
     * Helper::ENVIRONMENT_CODE_PROD with environment "0" applied.
     *
     * @return void
     * @throws MissingDataException
     * @throws ValidatorException
     */
    public function testGetEnvironmentCodeForProd(): void
    {
        $this->model->setEnvironment(0);

        self::assertSame(
            Helper::ENVIRONMENT_CODE_PROD,
            $this->helper->getEnvironmentCode($this->model)
        );
    }

    /**
     * Assert that the getEnvironmentCode method will result in an instance of
     * MissingDataException when attempting to resolve code value from an
     * instance of the Credentials model with no environment applied.
     *
     * @return void
     * @throws MissingDataException
     */
    public function testExceptionThrownWithoutEnvironmentWhenGettingCode(): void
    {
        $this->expectException(MissingDataException::class);

        $this->helper->getEnvironmentCode($this->model);
    }

    /**
     * Assert that the getMethodSuffix method will result in an instance of
     * MissingDataException when attempting to resolve a value from an instance
     * of the Credentials model with no environment applied.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testExceptionThrownWithoutEnvWhenGettingSuffix(): void
    {
        $this->expectException(MissingDataException::class);

        $this->model->setUsername('donny');

        $this->helper->getMethodSuffix($this->model);
    }

    /**
     * Assert that the getMethodSuffix method will result in an instance of
     * MissingDataException when attempting to resolve a value from an instance
     * of the Credentials model with no username applied.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testExceptionThrownWithoutUsernameWhenGettingSuffix(): void
    {
        $this->expectException(MissingDataException::class);

        $this->model->setEnvironment(1);

        $this->helper->getMethodSuffix($this->model);
    }

    /**
     * Assert that the getMethodSuffix method will result in the expected value
     * for a Credentials model instance with the corresponding values applied.
     *
     * @return void
     * @throws MissingDataException
     * @throws ValidatorException
     */
    public function testMethodSuffixResult(): void
    {
        $this->model
            ->setUsername('walter')
            ->setEnvironment(0);

        self::assertSame(
            'walter_' . Helper::ENVIRONMENT_CODE_PROD,
            $this->helper->getMethodSuffix($this->model)
        );
    }

    /**
     * Assert that the getMethodSuffix method will always result in a lowercase
     * value.
     *
     * @return void
     * @throws MissingDataException
     * @throws ValidatorException
     */
    public function testMethodSuffixResultIsLowerCase(): void
    {
        $this->model
            ->setUsername('BuNNy')
            ->setEnvironment(1);

        self::assertSame(
            'bunny_' . Helper::ENVIRONMENT_CODE_TEST,
            $this->helper->getMethodSuffix($this->model)
        );
    }
}
