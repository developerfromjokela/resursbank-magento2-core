<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model\Payment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Model\Payment\Resursbank as Method;

/**
 * Test cases designed for Payment\Item data model.
 */
class ResursbankTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Method
     */
    private $method;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->method = $this->objectManager->getObject(Method::class);
    }

    /**
     * Assert that the default title value is returned if nothing else has been
     * assigned.
     *
     * @return void
     */
    public function testDefaultTitle(): void
    {
        static::assertSame(Method::TITLE, $this->method->getTitle());
    }

    /**
     * Assert that the assigned title value is returned from getTitle().
     *
     * @return void
     */
    public function testAssignedTitle(): void
    {
        $this->method->setTitle('Invoice');

        static::assertSame('Invoice', $this->method->getTitle());
    }

    /**
     * Assert that getTitle() value is returned when getConfigData() is called
     * using 'title' as argument 1.
     *
     * @return void
     */
    public function testConfigDataTitle(): void
    {
        $this->method->setTitle('Part payment');

        static::assertSame(
            'Part payment',
            $this->method->getConfigData('title')
        );
    }
}
