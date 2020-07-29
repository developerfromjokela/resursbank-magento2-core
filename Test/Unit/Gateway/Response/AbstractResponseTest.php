<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Gateway\Http;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Gateway\Response\AbstractResponse;

/**
 * Test cases designed for Resursbank\Core\Gateway\Response\AbstractResponse
 *
 * @package Resursbank\Core\Test\Unit\Gateway\Response
 */
class AbstractResponseTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AbstractResponse
     */
    private $response;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Mock TransferFactory object (the target of our tests).
        $this->response = $this->getMockBuilder(AbstractResponse::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * Assert that the getReference method will throw an instance of
     * ValidatorException if it hasn't been supplied any reference.
     */
    public function testGetReferenceValidateWithoutReference(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing reference in response.');

        $this->response->getReference([]);
    }

    /**
     * Assert that the getReference method will throw an instance of
     * ValidatorException if it has been supplied reference with the wrong
     * data type.
     */
    public function testGetReferenceValidateType(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'Reference must be a string.'
        );

        $this->response->getReference(['reference' => true]);
    }

    /**
     * Assert that the getReference method will throw an instance of
     * ValidatorException if it has been supplied an empty reference value.
     */
    public function testGetReferenceValidateEmptyValue(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing reference value.');

        $this->response->getReference(['reference' => '']);
    }

    /**
     * Assert that the getReference method will resolve supplied reference from
     * anonymous array containing reference data.
     */
    public function testGetReference(): void
    {
        try {
            $result = $this->response->getReference(
                ['reference' => '45567824334545623579']
            );

            static::assertSame('45567824334545623579', $result);
        } catch (ValidatorException $e) {
            static::fail('Failed resolving reference: ' . $e->getMessage());
        }
    }

    /**
     * Assert that the wasSuccessful method will throw an instance of
     * ValidatorException if it hasn't been supplied any status.
     */
    public function testWasSuccessfulValidateWithoutStatus(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing status in response.');

        $this->response->wasSuccessful([]);
    }

    /**
     * Assert that the wasSuccessful method will throw an instance of
     * ValidatorException if it has been supplied status with the wrong
     * data type.
     */
    public function testWasSuccessfulValidateType(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'Status must be a bool.'
        );

        $this->response->wasSuccessful(['status' => 44]);
    }

    /**
     * Assert that the wasSuccessful method will resolve supplied status from
     * anonymous array containing response data.
     */
    public function testWasSuccessful(): void
    {
        try {
            $result = $this->response->wasSuccessful(
                ['status' => false]
            );

            static::assertFalse($result);
        } catch (ValidatorException $e) {
            static::fail('Failed resolving status: ' . $e->getMessage());
        }
    }
}
