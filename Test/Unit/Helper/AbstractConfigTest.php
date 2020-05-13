<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use Resursbank\Core\Helper\AbstractConfig;

/**
 * Test cases designed for Resursbank\Core\Helper\AbstractConfig
 *
 * @package Resursbank\Core\Test\Unit\Helper
 */
class AbstractConfigTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AbstractConfig
     */
    private $config;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->config = $this->getMockBuilder(AbstractConfig::class)
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
                $this->getPathMethod()->invoke($this->config, 'api', 'flow')
            );
        } catch (ReflectionException $e) {
            self::fail('Failed to create reflection method of getPath.');
        }
    }

    /**
     * Retrieve accessible getPath method mock.
     *
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    private function getPathMethod(): ReflectionMethod
    {
        $obj = new ReflectionObject($this->config);
        $method = $obj->getMethod('getPath');
        $method->setAccessible(true);

        return $method;
    }
}
