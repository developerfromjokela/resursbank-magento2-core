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
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $orderRepositoryMock = $this->createMock(OrderRepository::class);

        $this->orderHelper = new Order(
            $contextMock,
            $searchCriteriaBuilderMock,
            $orderRepositoryMock
        );
    }

    /**
     * Assert that the order supplied is new
     */
    public function testIsNew()
    {
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->expects(self::any())->method("isObjectNew")->willReturn(true);
        $orderMock->expects(self::any())->method("getOriginalIncrementId")->willReturn("");
        $orderMock->expects(self::any())->method("getGrandTotal")->willReturn(999);
        self::assertTrue($this->orderHelper->isNew($orderMock));
    }

    /**
     * Assert that the order supplied is old if isObjectNew returns false
     */
    public function testIsNewReturnsFalseIfIsObjectNewReturnsFalse()
    {
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->expects(self::any())->method("isObjectNew")->willReturn(false);
        $orderMock->expects(self::any())->method("getOriginalIncrementId")->willReturn("");
        $orderMock->expects(self::any())->method("getGrandTotal")->willReturn(999);
        self::assertFalse($this->orderHelper->isNew($orderMock));
    }

    /**
     * Assert that the order supplied is old if order has an incremented id
     */
    public function testIsNewReturnsFalseIfGrandTotalIsZero()
    {
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->expects(self::any())->method("isObjectNew")->willReturn(true);
        $orderMock->expects(self::any())->method("getOriginalIncrementId")->willReturn("100000120");
        $orderMock->expects(self::any())->method("getGrandTotal")->willReturn(0);
        self::assertFalse($this->orderHelper->isNew($orderMock));
    }


    /**
     * Assert that the order supplied is old if order has an incremented id
     */
    public function testIsNewReturnsFalseIfIncrementedIdExist()
    {
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->expects(self::any())->method("isObjectNew")->willReturn(true);
        $orderMock->expects(self::any())->method("getOriginalIncrementId")->willReturn("100000120");
        $orderMock->expects(self::any())->method("getGrandTotal")->willReturn(999);
        self::assertFalse($this->orderHelper->isNew($orderMock));
    }
}
