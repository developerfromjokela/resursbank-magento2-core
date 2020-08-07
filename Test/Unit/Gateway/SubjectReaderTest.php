<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Gateway\Command;

use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Gateway\SubjectReader;

/**
 * Test cases designed for Resursbank\Core\Gateway\Command\Gateway
 *
 * @package Resursbank\Core\Test\Unit\Gateway\Command
 */
class SubjectReaderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var PaymentDataObject
     */
    private $methodData;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Mock SubjectReader (the target or our tests).
        $this->subjectReader = $this->objectManager->getObject(
            SubjectReader::class
        );

        // Mock PaymentDataObject instance.
        $this->methodData = $this->objectManager->getObject(
            PaymentDataObject::class
        );
    }

    /**
     * Assert that the readPayment method will throw an instance of
     * ValidatorException if it's called without being supplied payment data.
     */
    public function testReadPaymentThrowsWithoutPaymentData(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->subjectReader->readPayment([]);
    }

    /**
     * Assert that the readPayment method will throw an instance of
     * ValidatorException if supplied payment data doesn't match the expected
     * type PaymentDataObjectInterface.
     */
    public function testReadPaymentThrowsWithUnexpectedPaymentData(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->subjectReader->readPayment(
            ['payment' => 'Not an instance of PaymentDataObject']
        );
    }

    /**
     * Assert that the readPayment method will return the supplied
     * PaymentDataObject instance from the supplied anonymous array.
     */
    public function testReadPaymentResolvesPaymentDataObject(): void
    {
        try {
            $result = $this->subjectReader->readPayment(
                ['payment' => $this->methodData]
            );

            static::assertSame($this->methodData, $result);
        } catch (InvalidArgumentException $e) {
            static::fail('Failed to resolve payment data: ' . $e->getMessage());
        }
    }

    /**
     * Assert that the readCredentials method will throw an instance of
     * ValidatorException if we supply it an empty array.
     */
    public function testReadCredentialsThrowsWithoutPayment(): void
    {
        $this->expectException(InvalidArgumentException::class);

        try {
            $this->subjectReader->readCredentials([]);
        } catch (ValidatorException $e) {
            static::fail($e->getMessage());
        } catch (NoSuchEntityException $e) {
            static::fail($e->getMessage());
        }
    }

    /**
     * Assert that the readReference method will throw an instance of
     * ValidatorException if we supply an empty array.
     */
    public function testReadReferenceThrowsWithoutReference(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing reference.');

        $this->subjectReader->readReference([]);
    }

    /**
     * Assert that the readReference method will throw an instance of
     * ValidatorException if the supplied reference is not a string.
     */
    public function testReadReferenceThrowsOnWrongType(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'Requested reference must be a string.'
        );

        $this->subjectReader->readReference(['reference' => 123]);
    }

    /**
     * Assert that the readReference method will throw an instance of
     * ValidatorException if we supply it an empty string.
     */
    public function testReadReferenceThrowsOnEmptyValue(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing reference value.');

        $this->subjectReader->readReference(['reference' => '']);
    }

    /**
     * Assert that the readReference method will resolve supplied reference from
     * anonymous array containing request data.
     */
    public function testReadReference(): void
    {
        try {
            $result = $this->subjectReader->readReference(
                ['reference' => '54565656223']
            );

            static::assertSame('54565656223', $result);
        } catch (ValidatorException $e) {
            static::fail('Failed resolving reference: ' . $e->getMessage());
        }
    }
}
