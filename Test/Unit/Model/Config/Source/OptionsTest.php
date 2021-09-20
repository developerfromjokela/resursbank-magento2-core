<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model\Config\Source;

use Resursbank\Core\Model\Config\Source\Options;
use PHPUnit\Framework\TestCase;

/**
 * Test cases designed for generic options class.
 */
class OptionsTest extends TestCase
{

    /**
     * @var Options
     */
    private $options;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        // Mock abstract Option class and whitelist toArray method so we can
        // mock its return value.
        $this->options = $this->getMockBuilder(Options::class)
            ->onlyMethods(['toArray'])
            ->getMockForAbstractClass();

        // Mock return value of toArray method, implemented by subclasses.
        $this->options->expects(static::once())
            ->method('toArray')
            ->willReturn(['test' => 'Test', 'production' => 'Production']);
    }

    /**
     * Assert toOptionArray return the same number of options as toArray.
     *
     * @return void
     */
    public function testHasSameNumberOfOptionsAsToArray(): void
    {
        static::assertCount(2, $this->options->toOptionArray());
    }

    /**
     * Assert that toOptionArray output format will fit the expected structure
     * for select elements in the configuration.
     *
     * @return void
     */
    public function testValueConversionFromToOptionArray(): void
    {
        static::assertSame([
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
