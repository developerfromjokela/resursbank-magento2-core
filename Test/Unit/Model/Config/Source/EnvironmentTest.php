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

namespace Resursbank\Core\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Resursbank\Core\Model\Config\Source\Environment;
use PHPUnit\Framework\TestCase;

/**
 * Test cases designed for environment options.
 *
 * @package Resursbank\Core\Test\Model\Config\Source
 */
class EnvironmentTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->environment = $this->objectManager
            ->getObject(Environment::class);
    }

    /**
     * Assert that environment options include "test".
     *
     * @return void
     */
    public function testHasTestOption(): void
    {
        $this->assertArrayHasKey('test', $this->environment->toArray());
    }

    /**
     * Assert that environment options include "production".
     *
     * @return void
     */
    public function testHasProductionOption(): void
    {
        $this->assertArrayHasKey('production', $this->environment->toArray());
    }

    /**
     * Assert that environment has exactly two options.
     *
     * @return void
     */
    public function testHasTwoOptions(): void
    {
        $this->assertCount(2, $this->environment->toArray());
    }
}
