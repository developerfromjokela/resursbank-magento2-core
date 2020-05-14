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

namespace Resursbank\Core\Test\Unit\Helper\PaymentMethods;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\PaymentMethods\Converter;

/**
 * Tests designed for payment method data conversion.
 *
 * @package Resursbank\Core\Test\Unit\Helper\PaymentMethods
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
     * @var array
     */
    private $apiData = [];

    /**
     * @var array
     */
    private $modelData;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->converter = $this->objectManager->getObject(Converter::class);
        $this->apiData = [
            Converter::KEY_ID => 'invoice',
            Converter::KEY_DESCRIPTION => 'My Invoice',
            Converter::KEY_MIN_LIMIT => '150',
            Converter::KEY_MAX_LIMIT => '160.50'
        ];
        $this->modelData = [
            Converter::MODEL_KEY_IDENTIFIER => $this->apiData[Converter::KEY_ID],
            Converter::MODEL_KEY_TITLE => $this->apiData[Converter::KEY_DESCRIPTION],
            Converter::MODEL_KEY_MIN_ORDER_TOTAL => $this->apiData[Converter::KEY_MIN_LIMIT],
            Converter::MODEL_KEY_MAX_ORDER_TOTAL => $this->apiData[Converter::KEY_MAX_LIMIT]
        ];
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
     * @throws ValidatorException
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
     */
    public function testConvert(): void
    {
        static::assertEquals(
            $this->modelData,
            $this->converter->convert($this->apiData)
        );
    }
}
