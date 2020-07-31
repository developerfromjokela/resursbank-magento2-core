<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Gateway\Command;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use Resursbank\Core\Gateway\Command\Gateway;
use Magento\Payment\Gateway\Data\Order\OrderAdapter as Order;

/**
 * Test cases designed for Resursbank\Core\Gateway\Command\Gateway
 *
 * @package Resursbank\Core\Test\Unit\Gateway\Command
 */
class GatewayTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var PaymentDataObject
     */
    private $methodData;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Mock OrderAdapter instance and publish getGrandTotalAmount method.
        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGrandTotalAmount'])
            ->getMock();

        // Mock Gateway (the target or our tests).
        $this->gateway = $this->objectManager->getObject(Gateway::class);

        // Mock PaymentDataObject instance.
        $this->methodData = $this->objectManager->getObject(
            PaymentDataObject::class,
            ['order' => $this->order]
        );
    }

    /**
     * Assert that the isEnabled method will result in false if the grand order
     * total of the supplied Order is 0.
     */
    public function testIsEnabledReturnsFalseWithZeroOrderTotal(): void
    {
        $this->order->expects(static::once())
            ->method('getGrandTotalAmount')
            ->willReturn(0);

        try {
            static::assertFalse(
                $this->getIsEnabledMethod()->invoke(
                    $this->gateway,
                    $this->methodData
                )
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to mock isEnabled method: ' . $e->getMessage()
            );
        }
    }

    /**
     * Assert that the isEnabled method will result in true if the grand order
     * total of the supplied Order exceeds 0.
     */
    public function testIsEnabledReturnsTrueWhenOrderTotalExceedZero(): void
    {
        $this->order->expects(static::once())
            ->method('getGrandTotalAmount')
            ->willReturn(15.55);

        try {
            static::assertTrue(
                $this->getIsEnabledMethod()->invoke(
                    $this->gateway,
                    $this->methodData
                )
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to mock isEnabled method: ' . $e->getMessage()
            );
        }
    }

    /**
     * Assert that the getPayment method will throw an instance of
     * ValidatorException if it's called without being supplied payment data.
     */
    public function testGetPaymentThrowsWithoutPaymentData(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing payment data.');

        $this->gateway->getPayment([]);
    }

    /**
     * Assert that the getPayment method will throw an instance of
     * ValidatorException if supplied payment data doesn't match the expected
     * type PaymentDataObjectInterface.
     */
    public function testGetPaymentThrowsWithUnexpectedPaymentData(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'Payment data must be instance of ' . PaymentDataObject::class
        );

        $this->gateway->getPayment(
            ['payment' => 'Not an instance of PaymentDataObject']
        );
    }

    /**
     * Assert that the getPayment method will return the supplied
     * PaymentDataObject instance from the supplied anonymous array.
     */
    public function testGetPaymentResolvesPaymentDataObject(): void
    {
        try {
            $result = $this->gateway->getPayment(
                ['payment' => $this->methodData]
            );

            static::assertSame($this->methodData, $result);
        } catch (ValidatorException $e) {
            static::fail('Failed to resolve payment data: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve accessible isEnabled method mock.
     *
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    private function getIsEnabledMethod(): ReflectionMethod
    {
        $obj = new ReflectionObject($this->gateway);
        $method = $obj->getMethod('isEnabled');
        $method->setAccessible(true);

        return $method;
    }
}
