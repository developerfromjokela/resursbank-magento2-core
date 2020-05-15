<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper;

use Exception;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\RBEcomPHP\ResursBank;

/**
 * Test cases designed for Resursbank\Core\Helper\PaymentMethods
 *
 * @package Resursbank\Core\Test\Unit\Helper
 */
class PaymentMethodsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var ResursBank
     */
    private $connection;

    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Mock the API helper class so we can later modify the behaviour of
        // the getConnection method.
        $this->api = $this->getMockBuilder(Api::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMock();

        // Mock instance of ResursBank class (API connection) so we can later
        // modify the behaviour of the getPaymentMethods method.
        $this->connection = $this->getMockBuilder(ResursBank::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPaymentMethods'])
            ->getMock();

        // Create mocked, empty, instance of Credentials model.
        $this->credentials = $this->objectManager
            ->getObject(Credentials::class);
    }

    /**
     * Assert that the fetch method throws an instance of IntegrationException
     * when an Exception occurs while attempting to fetch a list of available
     * payment methods from the API. Also ensure that the original Exception
     * message from Resurs Bank is forwarded unmodified.
     *
     * @return void
     */
    public function testFetchThrowsIntegrationExceptionOnFailure(): void
    {
        $this->expectException(IntegrationException::class);
        $this->expectExceptionMessage('Some connection error.');

        // Make the getConnection method on our API adapter toss an Exception.
        $this->api->expects(static::once())
            ->method('getConnection')
            ->will(static::throwException(
                new Exception('Some connection error.')
            ));

        /** @var PaymentMethods $methods */
        $methods = $this->objectManager
            ->getObject(PaymentMethods::class, ['api' => $this->api]);

        $methods->fetch($this->credentials);
    }

    /**
     * Assert that the fetch method throws an instance of IntegrationException
     * when ECom relies anything that isn't an array from an API call to fetch a
     * list of available payment methods.
     *
     * @return void
     */
    public function testFetchThrowsIntegrationExceptionOnInaccurateData(): void
    {
        $this->expectException(IntegrationException::class);
        $this->expectExceptionMessage(
            'Failed to fetch payment methods from API. Expected Array.'
        );

        // Modify return value of getPaymentMethods method from the API class.
        $this->connection->expects(static::any())
            ->method('getPaymentMethods')
            ->willReturn('This is not an array.');

        // Make sure our API adapter returns our mocked API class instance.
        $this->api->expects(static::once())
            ->method('getConnection')
            ->willReturn($this->connection);

        /** @var PaymentMethods $methods */
        $methods = $this->objectManager
            ->getObject(PaymentMethods::class, ['api' => $this->api]);

        $methods->fetch($this->credentials);
    }

    /**
     * Assert that the fetch method returns the value from a getPaymentMethods
     * API call, unmodified.
     *
     * @return void
     */
    public function testFetchReturnValue(): void
    {
        // Expected data returned from the API when fetching payment methods.
        $methodsData = [
            (object) [
                'identifier' => 'invoice',
                'description' => 'test',
                'minLimit' => 5.0,
                'maxLimit' => 10.0
            ],
            (object) [
                'identifier' => 'partpayment',
                'description' => 'testing_more',
                'minLimit' => 100.0,
                'maxLimit' => 50000.0
            ]
        ];

        // Modify return value of getPaymentMethods method from the API class.
        $this->connection->expects(static::any())
            ->method('getPaymentMethods')
            ->willReturn(
                $methodsData
            );

        // Make sure our API adapter returns our mocked API class instance.
        $this->api->expects(static::once())
            ->method('getConnection')
            ->willReturn($this->connection);

        /** @var PaymentMethods $methods */
        $methods = $this->objectManager
            ->getObject(PaymentMethods::class, ['api' => $this->api]);

        try {
            // Assert our fetch method does not alter the data retrieved through
            // the API adapter.
            static::assertSame($methodsData, $methods->fetch($this->credentials));
        } catch (IntegrationException $e) {
            static::fail('Failed asserting return value of the fetch method.');
        }
    }
}
