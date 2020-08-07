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
use Resursbank\Core\Helper\Api;

/**
 * Test cases designed for the Api service.
 */
class ApiTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Api
     */
    private $api;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->api = $this->objectManager->getObject(Api::class);
    }

    /**
     * Assert that getUserAgent returns default value when no custom value is
     * supplied.
     *
     * @return void
     */
    public function testGetUserAgentReturnsWithoutCustom(): void
    {
        try {
            static::assertSame(
                'Mage 2',
                $this->getGetUserAgentMethod($this->api)->invoke($this->api, '')
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to resolve getUserAgent method mock: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that the getUserAgent method include provided custom string in
     * output.
     *
     * @return void
     */
    public function testGetUserAgentReturnsWithCustom(): void
    {
        try {
            static::assertSame(
                'Mage 2 + Some custom action value',
                $this->getGetUserAgentMethod($this->api)->invoke(
                    $this->api,
                    'Some custom action value'
                )
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to resolve getUserAgent method mock: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Retrieve accessible getUserAgent method mock.
     *
     * @param Api $obj
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    private function getGetUserAgentMethod(
        Api $obj
    ): ReflectionMethod {
        $obj = new ReflectionObject($obj);
        $method = $obj->getMethod('getUserAgent');
        $method->setAccessible(true);

        return $method;
    }
}
