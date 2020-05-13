<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Resursbank\Core\Model\Config\Source\Options;
use PHPUnit\Framework\TestCase;

/**
 * Test cases designed for generic options class.
 *
 * @package Resursbank\Core\Test\Unit\Model\Config\Source
 */
class OptionsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Options
     */
    private $options;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Mock abstract Option class and whitelist toArray method so we can
        // mock its return value.
        $this->options = $this->getMockBuilder(Options::class)
            ->setMethods(['toArray'])
            ->getMockForAbstractClass();

        // Mock return value of toArray method, implemented by subclasses.
        $this->options->expects(self::once())
            ->method('toArray')
            ->will(self::returnValue([
                'test' => 'Test',
                'production' => 'Production'
            ]));
    }

    /**
     * Assert toOptionArray return the same number of options as toArray.
     *
     * @return void
     */
    public function testHasSameNumberOfOptionsAsToArray(): void
    {
        self::assertCount(2, $this->options->toOptionArray());
    }

    /**
     * Assert that toOptionArray output format will fit the expected structure
     * for select elements in the configuration.
     *
     * @return void
     */
    public function testValueConversionFromToOptionArray(): void
    {
        self::assertSame([
            [
                'value' => 'test',
                'label' => 'Test'
            ],
            [
                'value' => 'production',
                'label' => 'Production'
            ]
        ], $this->options->toOptionArray());
    }
}
