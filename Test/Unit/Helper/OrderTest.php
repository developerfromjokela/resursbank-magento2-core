<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

namespace Resursbank\Core\Test\Unit\Helper;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\OrderRepository;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Order;

class OrderTest extends TestCase
{
    private Order $orderHelper;

    /**
     * @inheriDoc
     */
    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $criteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $orderRepositoryMock = $this->createMock(OrderRepository::class);

        $this->orderHelper = new Order(
            $contextMock,
            $criteriaBuilderMock,
            $orderRepositoryMock
        );
    }

    /**
     * Assert that the order supplied is new.
     */
    public function testIsNew(): void
    {
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->method('isObjectNew')->willReturn(true);
        $orderMock->method('getOriginalIncrementId')->willReturn('');
        $orderMock->method('getGrandTotal')->willReturn(999);
        self::assertTrue($this->orderHelper->isNew($orderMock));
    }

    /**
     * Assert that the order supplied is old if isObjectNew returns false.
     */
    public function testIsNewReturnsFalseIfIsObjectNewReturnsFalse(): void
    {
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->method('isObjectNew')->willReturn(false);
        $orderMock->method('getOriginalIncrementId')->willReturn('');
        $orderMock->method('getGrandTotal')->willReturn(999);
        self::assertFalse($this->orderHelper->isNew($orderMock));
    }

    /**
     * Assert that the order supplied is old if order has an incremented id.
     */
    public function testIsNewReturnsFalseIfGrandTotalIsZero(): void
    {
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->method('isObjectNew')->willReturn(true);
        $orderMock->method('getOriginalIncrementId')->willReturn('100000120');
        $orderMock->method('getGrandTotal')->willReturn(0);
        self::assertFalse($this->orderHelper->isNew($orderMock));
    }


    /**
     * Assert that the order supplied is old if order has an incremented id.
     */
    public function testIsNewReturnsFalseIfIncrementedIdExist(): void
    {
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->method('isObjectNew')->willReturn(true);
        $orderMock->method('getOriginalIncrementId')->willReturn('100000120');
        $orderMock->method('getGrandTotal')->willReturn(999);
        self::assertFalse($this->orderHelper->isNew($orderMock));
    }
}
