<?php
/**
 * Copyright 2016 Resurs Bank AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Resursbank\Core\Model\Config\Source\Options;
use PHPUnit\Framework\TestCase;

/**
 * Test cases designed for generic options collection class.
 *
 * @package Resursbank\Core\Test\Model\Config\Source
 */
class OptionsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject
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
        $this->options->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue([
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
        $this->assertCount(2, $this->options->toOptionArray());
    }

    /**
     * AsserttoOptionArray output format. This should fit the expected structure
     * for select elements in the configuration.
     *
     * @return void
     */
    public function testValueConversionFromToOptionArray(): void
    {
        $this->assertEquals([
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
