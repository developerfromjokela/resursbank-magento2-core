<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Gateway\Http;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Gateway\Http\TransferFactory;
use Resursbank\Core\Helper\Api\Credentials as CredentialsHelper;
use Resursbank\Core\Model\Api\Credentials;
use Magento\Payment\Gateway\Http\TransferBuilder;

/**
 * Test cases designed for Resursbank\Core\Gateway\Http\TransferFactory
 *
 * @package Resursbank\Core\Test\Unit\Gateway\Http
 */
class TransferFactoryTest extends TestCase
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
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @var CredentialsHelper
     */
    private $credentialsHelper;

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Mock CredentialsHelper for indirect tests.
        $this->credentialsHelper = $this->objectManager->getObject(
            CredentialsHelper::class,
        );

        // Mock TransferBuilder for indirect tests.
        $this->transferBuilder = $this->objectManager->getObject(
            TransferBuilder::class
        );

        // Mock TransferFactory (the target of our tests).
        $this->transferFactory = $this->objectManager->getObject(
            TransferFactory::class,
            [
                'transferBuilder' => $this->transferBuilder,
                'credentialsHelper' => $this->credentialsHelper
            ]
        );

        // Mock Credentials.
        $this->credentials = $this->objectManager->getObject(
            Credentials::class
        );
    }

    /**
     * Assert that the getCredentials method will throw an instance of
     * ValidatorException if we supply it an empty array.
     */
    public function testGetCredentialsThrowsWithoutCredentials(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing credentials in request.');

        $this->transferFactory->getCredentials([]);
    }

    /**
     * Assert that the getCredentials method will throw an instance of
     * ValidatorException if the supplied credentials is not an instance of
     * Credentials.
     */
    public function testGetCredentialsThrowsOnWrongType(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'Request credentials must be of type ' . Credentials::class
        );

        $this->transferFactory->getCredentials(['credentials' => true]);
    }

    /**
     * Assert that the getCredentials method will throw an instance of
     * ValidatorException if the supplied Credentials instance is missing a
     * username.
     */
    public function testGetCredentialsThrowsWithoutUsername(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Incomplete request credentials.');

        $this->credentials->setPassword('testing');

        $this->transferFactory->getCredentials(
            ['credentials' => $this->credentials]
        );
    }

    /**
     * Assert that the getCredentials method will throw an instance of
     * ValidatorException if the supplied Credentials instance is missing a
     * password.
     */
    public function testGetCredentialsValidateWithoutPassword(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Incomplete request credentials.');

        $this->credentials->setUsername('puck');

        $this->transferFactory->getCredentials(
            ['credentials' => $this->credentials]
        );
    }

    /**
     * Assert that the getCredentials method will return an instance of
     * Credentials resolved from an anonymous array.
     */
    public function testGetCredentials(): void
    {
        try {
            $this->credentials
                ->setUsername('rambo')
                ->setPassword('alwaysblue');

            $result = $this->transferFactory->getCredentials(
                ['credentials' => $this->credentials]
            );

            static::assertSame($this->credentials, $result);
        } catch (ValidatorException $e) {
            static::fail('Failed resolving credentials: ' . $e->getMessage());
        }
    }

    /**
     * Assert that the getReference method will throw an instance of
     * ValidatorException if we supply an empty array.
     */
    public function testGetReferenceThrowsWithoutReference(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing reference in request.');

        $this->transferFactory->getReference([]);
    }

    /**
     * Assert that the getReference method will throw an instance of
     * ValidatorException if the supplied reference is not a string.
     */
    public function testGetReferenceThrowsOnWrongType(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'Requested reference must be a string.'
        );

        $this->transferFactory->getReference(['reference' => 123]);
    }

    /**
     * Assert that the getReference method will throw an instance of
     * ValidatorException if we supply it an empty string.
     */
    public function testGetReferenceThrowsOnEmptyValue(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing reference value.');

        $this->transferFactory->getReference(['reference' => '']);
    }

    /**
     * Assert that the getReference method will resolve supplied reference from
     * anonymous array containing request data.
     */
    public function testGetReference(): void
    {
        try {
            $result = $this->transferFactory->getReference(
                ['reference' => '54565656223']
            );

            static::assertSame('54565656223', $result);
        } catch (ValidatorException $e) {
            static::fail('Failed resolving reference: ' . $e->getMessage());
        }
    }

    /**
     * Assert that the create method will return an instance of
     * TransferInterface with the correct data applied.
     */
    public function testCreate(): void
    {
        $result = null;
        $reference = '12346567835667231';

        try {
            $this->credentials
                ->setUsername('megastore')
                ->setPassword('12pw54!');

            $result = $this->transferFactory->create([
                'credentials' => $this->credentials,
                'reference' => $reference
            ]);
        } catch (ValidatorException $e) {
            static::fail('Method create failed: ' . $e->getMessage());
        }

        $clientConfig = $result->getClientConfig();
        $body = $result->getBody();

        static::assertArrayHasKey('credentials', $clientConfig);
        static::assertSame($this->credentials, $clientConfig['credentials']);
        static::assertArrayHasKey('reference', $body);
        static::assertSame($reference, $body['reference']);
    }
}
