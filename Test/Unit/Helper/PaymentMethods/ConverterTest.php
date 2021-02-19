<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper\PaymentMethods;

use JsonException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\PaymentMethods\Converter;

/**
 * Tests designed for payment method data conversion.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ConverterTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var array<string, string>
     */
    private $apiData = [];

    /**
     * @var array<string, mixed>
     */
    private $modelData;

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        /** @phpstan-ignore-next-line */
        $this->converter = $this->objectManager->getObject(Converter::class);

        $this->apiData = [
            Converter::KEY_ID => 'invoice',
            Converter::KEY_DESCRIPTION => 'My Invoice',
            Converter::KEY_MIN_LIMIT => '150',
            Converter::KEY_MAX_LIMIT => '160.50'
        ];

        $this->modelData = [
            PaymentMethodInterface::IDENTIFIER => $this->apiData[
                Converter::KEY_ID
            ],
            PaymentMethodInterface::TITLE => $this->apiData[
                Converter::KEY_DESCRIPTION
            ],
            PaymentMethodInterface::MIN_ORDER_TOTAL => (float) $this->apiData[
                Converter::KEY_MIN_LIMIT
            ],
            PaymentMethodInterface::MAX_ORDER_TOTAL => (float) $this->apiData[
                Converter::KEY_MAX_LIMIT
            ]
        ];

        $this->modelData[PaymentMethodInterface::RAW] =
            json_encode($this->apiData, JSON_THROW_ON_ERROR);
    }

    /**
     * Assert that getIdentifier will return value when it's included.
     *
     * @return void
     */
    public function testGetIdentifierReturnsValueWithData(): void
    {
        static::assertSame(
            $this->apiData[Converter::KEY_ID],
            $this->converter->getIdentifier($this->apiData)
        );
    }

    /**
     * Assert that getIdentifier will return null when value is not included.
     *
     * @return void
     */
    public function testGetIdentifierReturnsNullWithoutData(): void
    {
        $data = $this->apiData;
        unset($data[Converter::KEY_ID]);

        static::assertNull($this->converter->getIdentifier($data));
    }

    /**
     * Assert that getIdentifier will return null when value is not a string.
     *
     * @return void
     */
    public function testGetIdentifierReturnsNullWithoutString(): void
    {
        $data = $this->apiData;
        $data[Converter::KEY_ID] = 55.0;

        static::assertNull($this->converter->getIdentifier($data));
    }

    /**
     * Assert that getIdentifier will return null when value is empty.
     *
     * @return void
     */
    public function testGetIdentifierReturnsNullWithEmptyValue(): void
    {
        $data = $this->apiData;
        $data[Converter::KEY_ID] = '';

        static::assertNull($this->converter->getIdentifier($data));
    }

    /**
     * Assert that getDescription will return value when it's included.
     *
     * @return void
     */
    public function testGetDescriptionReturnsValueWithData(): void
    {
        static::assertSame(
            $this->apiData[Converter::KEY_DESCRIPTION],
            $this->converter->getDescription($this->apiData)
        );
    }

    /**
     * Assert that getDescription will return default value when value is not
     * included.
     *
     * @return void
     */
    public function testGetDescriptionReturnsDefaultValueWithoutData(): void
    {
        $data = $this->apiData;
        unset($data[Converter::KEY_DESCRIPTION]);

        static::assertSame(
            Converter::DEFAULT_DESCRIPTION,
            $this->converter->getDescription($data)
        );
    }

    /**
     * Assert that getDescription will return default value when value is not
     * a string.
     *
     * @return void
     */
    public function testGetDescriptionReturnsDefaultValueWithoutString(): void
    {
        $data = $this->apiData;
        $data[Converter::KEY_DESCRIPTION] = true;

        static::assertSame(
            Converter::DEFAULT_DESCRIPTION,
            $this->converter->getDescription($data)
        );
    }

    /**
     * Assert that getDescription will return default value when value is empty.
     *
     * @return void
     */
    public function testGetDescriptionReturnsDefaultValueWithEmptyString(): void
    {
        $data = $this->apiData;
        $data[Converter::KEY_DESCRIPTION] = '';

        static::assertSame(
            Converter::DEFAULT_DESCRIPTION,
            $this->converter->getDescription($data)
        );
    }

    /**
     * Assert that getMinLimit will return value cast as float when value is
     * included.
     *
     * @return void
     */
    public function testGetMinLimitReturnsValueWithData(): void
    {
        static::assertSame(
            (float) $this->apiData[Converter::KEY_MIN_LIMIT],
            $this->converter->getMinLimit($this->apiData)
        );
    }

    /**
     * Assert that getMinLimit will return default value when value is not
     * included.
     *
     * @return void
     */
    public function testGetMinLimitReturnsDefaultValueWithoutData(): void
    {
        $data = $this->apiData;
        unset($data[Converter::KEY_MIN_LIMIT]);

        static::assertSame(
            Converter::DEFAULT_MIN_LIMIT,
            $this->converter->getMinLimit($data)
        );
    }

    /**
     * Assert that getMinLimit will return default value when value is not
     * numeric.
     *
     * @return void
     */
    public function testGetMinLimitReturnsDefaultValueWithoutNumeric(): void
    {
        $data = $this->apiData;
        $data[Converter::KEY_MIN_LIMIT] = false;

        static::assertSame(
            Converter::DEFAULT_MIN_LIMIT,
            $this->converter->getMinLimit($data)
        );
    }

    /**
     * Assert that getMinLimit will return value cast as float when it's
     * numeric.
     *
     * @return void
     */
    public function testGetMinLimitReturnsFloatValueWithNumeric(): void
    {
        $data = $this->apiData;
        $data[Converter::KEY_MIN_LIMIT] = '4.56';

        static::assertSame(
            4.56,
            $this->converter->getMinLimit($data)
        );
    }

    /**
     * Assert that getMaxLimit will return value when it's included.
     *
     * @return void
     */
    public function testGetMaxLimitReturnsValueWithData(): void
    {
        static::assertSame(
            (float) $this->apiData[Converter::KEY_MAX_LIMIT],
            $this->converter->getMaxLimit($this->apiData)
        );
    }

    /**
     * Assert that getMaxLimit will return default value when value is not
     * included.
     *
     * @return void
     */
    public function testGetMaxLimitReturnsDefaultValueWithoutData(): void
    {
        $data = $this->apiData;
        unset($data[Converter::KEY_MAX_LIMIT]);

        static::assertSame(
            Converter::DEFAULT_MAX_LIMIT,
            $this->converter->getMaxLimit($data)
        );
    }

    /**
     * Assert that getMaxLimit will return default value when value is not
     * numeric.
     *
     * @return void
     */
    public function testGetMaxLimitReturnsDefaultValueWithoutNumeric(): void
    {
        $data = $this->apiData;
        $data[Converter::KEY_MAX_LIMIT] = 'yada';

        static::assertSame(
            Converter::DEFAULT_MAX_LIMIT,
            $this->converter->getMaxLimit($data)
        );
    }

    /**
     * Assert that getMaxLimit will return value cast as float when it's
     * numeric.
     *
     * @return void
     */
    public function testGetMaxLimitReturnsFloatValueWithNumeric(): void
    {
        $data = $this->apiData;
        $data[Converter::KEY_MAX_LIMIT] = '10.12';

        static::assertSame(
            10.12,
            $this->converter->getMaxLimit($data)
        );
    }

    /**
     * Assert that the convert method casts a ValidatorException instance when
     * validation fails prior to conversion.
     *
     * @throws JsonException
     */
    public function testConvertThrowsValidatorExceptionWithFaultyData(): void
    {
        $data = $this->apiData;
        unset($data[Converter::KEY_ID]);

        $this->expectException(ValidatorException::class);

        $this->converter->convert($data);
    }

    /**
     * Assert that the convert method works (ie. that it converts data from the
     * API as expected).
     *
     * @throws ValidatorException
     * @throws JsonException
     */
    public function testConvert(): void
    {
        static::assertEquals(
            $this->modelData,
            $this->converter->convert($this->apiData)
        );
    }
}
