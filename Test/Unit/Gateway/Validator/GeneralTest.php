<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Gateway\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use Resursbank\Core\Gateway\Validator\General as Validator;

/**
 * Test cases designed for Resursbank\Core\Gateway\Validator\General
 *
 * @package Resursbank\Core\Test\Unit\Gateway\Validator
 */
class GeneralTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Mock Validator (the target of our tests).
        $this->validator = $this->objectManager->getObject(Validator::class);
    }

    /**
     * Assert that the wasSuccessful method resolves status value from anonymous
     * array.
     */
    public function testWasSuccessful(): void
    {
        try {
            static::assertTrue(
                $this->getWasSuccessful()->invoke($this->validator, [
                    'response' => [
                        'status' => true
                    ]
                ])
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to mock wasSuccessful method: ' . $e->getMessage()
            );
        }
    }

    /**
     * Retrieve accessible wasSuccessful method mock.
     *
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    private function getWasSuccessful(): ReflectionMethod
    {
        $obj = new ReflectionObject($this->validator);
        $method = $obj->getMethod('wasSuccessful');
        $method->setAccessible(true);

        return $method;
    }
}
