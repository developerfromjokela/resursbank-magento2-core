<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use Resursbank\Core\Helper\AbstractConfig;

class AbstractConfigTest extends TestCase
{
    /**
     * @var AbstractConfig|MockObject
     */
    private $configMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(AbstractConfig::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * Assert that the method getPath returns a correctly formatted path to the
     * request configuration value.
     *
     * @return void
     */
    public function testGetPath(): void
    {
        try {
            static::assertSame(
                'resursbank/api/flow',
                $this->getPathMethod()->invoke($this->configMock, 'api', 'flow')
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to create reflection method of getPath: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Retrieve accessible getPath method mock.
     *
     * @return ReflectionMethod
     */
    private function getPathMethod(): ReflectionMethod
    {
        $obj = new ReflectionObject($this->configMock);
        $method = $obj->getMethod('getPath');
        $method->setAccessible(true);

        return $method;
    }
}
