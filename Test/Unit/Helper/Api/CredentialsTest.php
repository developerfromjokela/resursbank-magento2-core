<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper\Api;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\ValidatorException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Config;
use Resursbank\RBEcomPHP\ResursBank;

class CredentialsTest extends TestCase
{

    private \Resursbank\Core\Model\Api\Credentials $credentialsModel;

    private Credentials $credentialsHelper;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $scopeConfigInterfaceMock;

    protected function setUp(): void
    {
        $objectManager = ObjectManager::getInstance();
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $contextMock = $this->createMock(Context::class);
        $writerInterfaceMock = $this->createMock(WriterInterface::class);
        $this->scopeConfigInterfaceMock = $this->createMock(ScopeConfigInterface::class);
        $resursConfig =  new Config(
            $this->scopeConfigInterfaceMock,
            $writerInterfaceMock,
            $contextMock
        );

        $this->credentialsModel = new \Resursbank\Core\Model\Api\Credentials();

        $this->credentialsHelper = new Credentials(
            $contextMock,
            $resursConfig,
            $objectManager,
            $storeManagerMock
        );

        parent::setUp();
    }

    /**
     * Assert that hasCredentials method will result in "true" if username and password
     * value has been applied on the Credentials model instance.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testHasCredentialsTrueWithUsernameAndPassword(): void
    {
        $this->credentialsModel->setUsername("username");
        $this->credentialsModel->setPassword("password");

        self::assertTrue($this->credentialsHelper->hasCredentials($this->credentialsModel));
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
        $this->credentialsModel->setPassword("password");

        self::assertFalse($this->credentialsHelper->hasCredentials($this->credentialsModel));
    }

    /**
     * Assert that hasCredentials method will result in "false" if no password
     * value has been applied on the Credentials model instance.
     *
     * @return void
     * @throws ValidatorException
     */
    public function testHasCredentialsFalseWithoutPassword(): void
    {
        $this->credentialsModel->setUsername("username");

        self::assertFalse($this->credentialsHelper->hasCredentials($this->credentialsModel));
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

        $this->credentialsModel->setEnvironment(1);

        $this->credentialsHelper->getHash($this->credentialsModel);
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

        $this->credentialsModel->setUsername('testing');

        $this->credentialsHelper->getHash($this->credentialsModel);
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
        $this->credentialsModel
            ->setUsername('username')
            ->setEnvironment(1);

        static::assertSame(
            '031796799e76cf794757b4cd59bd4eb7d0970abb',
            $this->credentialsHelper->getHash($this->credentialsModel)
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

        $this->credentialsModel->setUsername('username');

        $this->credentialsHelper->getMethodSuffix($this->credentialsModel);
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

        $this->credentialsModel->setEnvironment(1);

        $this->credentialsHelper->getMethodSuffix($this->credentialsModel);
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
        $this->credentialsModel
            ->setUsername('username')
            ->setEnvironment(0);

        static::assertSame(
            'username_' . ResursBank::ENVIRONMENT_PRODUCTION,
            $this->credentialsHelper->getMethodSuffix($this->credentialsModel)
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
        $this->credentialsModel
            ->setUsername('username')
            ->setEnvironment(1);

        static::assertSame(
            'username_' . ResursBank::ENVIRONMENT_TEST,
            $this->credentialsHelper->getMethodSuffix($this->credentialsModel)
        );
    }


    /**
     * Assert that the return value from resolveFromConfig returns the correct instance type
     *
     * @return void
     * @throws ValidatorException
     */
    public function testResolveFromConfigReturnsCorrectClass()
    {
        $storeCode = 'test_store_view';
        $scopeType = ScopeInterface::SCOPE_STORE;
        $this->scopeConfigInterfaceMock->method('getValue')->withConsecutive(
            ["resursbank/api/environment"],
            ["resursbank/api/environment"],
            ["resursbank/api/username_1"],
            ["resursbank/api/environment"],
            ["resursbank/api/password_1"],
            ["general/country/default"]
        )->willReturnOnConsecutiveCalls("1", "1",  "username", "1", "password", "SE");

        $result = $this->credentialsHelper->resolveFromConfig($storeCode, $scopeType);
        self::assertTrue($result instanceof  \Resursbank\Core\Model\Api\Credentials);
    }

    /**
     * Assert that an exception is thrown if general/country/default returns empty value
     *
     * @return void
     * @throws ValidatorException
     */
    public function testResolveFromConfigThrowsExceptionWithoutDefaultCountry()
    {
        $this->expectException(ValidatorException::class);
        $this->expectErrorMessage('Failed to apply country to Credentials instance.');
        $storeCode = 'test_store_view';
        $scopeType = ScopeInterface::SCOPE_STORE;
        $this->scopeConfigInterfaceMock->method('getValue')->withConsecutive(
            ["resursbank/api/environment"],
            ["resursbank/api/environment"],
            ["resursbank/api/username_1"],
            ["resursbank/api/environment"],
            ["resursbank/api/password_1"],
            ["general/country/default"]
        )->willReturnOnConsecutiveCalls("1", "1",  "username", "1", "password", "");

        $this->credentialsHelper->resolveFromConfig($storeCode, $scopeType);
    }
}
