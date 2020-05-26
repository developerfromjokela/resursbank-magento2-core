<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper\Api;

use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Api\Credentials as Helper;
use Resursbank\Core\Model\Api\Credentials as Model;
use Resursbank\RBEcomPHP\RESURS_ENVIRONMENTS;

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
     * @var Store
     */
    private $store;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(Model::class);
        $this->helper = $this->objectManager->getObject(Helper::class);
        $this->store = $this->objectManager->getObject(Store::class);
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

        static::assertTrue($this->helper->hasCredentials($this->model));
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

        static::assertFalse($this->helper->hasCredentials($this->model));
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

        static::assertFalse($this->helper->hasCredentials($this->model));
    }

    /**
     * Assert that attempting to generate a hash without a username results in
     * an instance of ValidatorException.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testExceptionThrownWhenGeneratingHashWithoutUsername(): void
    {
        $this->expectException(ValidatorException::class);

        $this->model->setEnvironment(1);

        $this->helper->getHash($this->model);
    }

    /**
     * Assert that attempting to generate a hash without an environment results
     * in an instance of ValidatorException.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testExceptionThrownWhenGeneratingHashWithoutEnv(): void
    {
        $this->expectException(ValidatorException::class);

        $this->model->setUsername('testing');

        $this->helper->getHash($this->model);
    }

    /**
     * Assert that the getHash method returns the expected result when username
     * and environment are applied on the supplied Credentials model instance.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testHashValue(): void
    {
        $this->model
            ->setUsername('testaccount')
            ->setEnvironment(1);

        static::assertSame(
            'a8c850514b63b1c6513ddd19599e9235c93ccd0b',
            $this->helper->getHash($this->model)
        );
    }

    /**
     * Assert that the getMethodSuffix method will result in an instance of
     * ValidatorException when attempting to resolve a value from an instance
     * of the Credentials model with no environment applied.
     *
     * @return void
     */
    public function testExceptionThrownWithoutEnvWhenGettingSuffix(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'Failed to resolve method suffix. Missing environment.'
        );

        $this->model->setUsername('janus');

        $this->helper->getMethodSuffix($this->model);
    }

    /**
     * Assert that the getMethodSuffix method will result in an instance of
     * ValidatorException when attempting to resolve a value from an instance
     * of the Credentials model with no username applied.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testExceptionThrownWithoutUsernameWhenGettingSuffix(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'Failed to resolve method suffix. Missing username.'
        );

        $this->model->setEnvironment(1);

        $this->helper->getMethodSuffix($this->model);
    }

    /**
     * Assert that the getMethodSuffix method will result in the expected value
     * for a Credentials model instance with the corresponding values applied.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testMethodSuffixResult(): void
    {
        $this->model
            ->setUsername('walter')
            ->setEnvironment(0);

        static::assertSame(
            'walter_' . RESURS_ENVIRONMENTS::PRODUCTION,
            $this->helper->getMethodSuffix($this->model)
        );
    }

    /**
     * Assert that the getMethodSuffix method will always result in a lowercase
     * value.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testMethodSuffixResultIsLowerCase(): void
    {
        $this->model
            ->setUsername('BuNNy')
            ->setEnvironment(1);

        static::assertSame(
            'bunny_' . RESURS_ENVIRONMENTS::TEST,
            $this->helper->getMethodSuffix($this->model)
        );
    }

    /**
     * Assert that an instance of StateException is thrown when there is no
     * Store instance applied on a Credentials data model instance.
     *
     * @throws StateException
     */
    public function testGetCountryThrowsWithoutStore(): void
    {
        $this->expectException(StateException::class);

        $this->helper->getCountry($this->model);
    }
}
