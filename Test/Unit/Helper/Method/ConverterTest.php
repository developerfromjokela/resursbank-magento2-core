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

namespace Resursbank\Core\Test\Unit\Helper\Method;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Method\Converter;
use Magento\Framework\Exception\ValidatorException;

/**
 * Convert Resurs Bank API data of a payment method to an actual payment method
 * object which can be interpreted by Magento.
 *
 * @package Resursbank\Core\Helper
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
     * Assert that getIdentifier will return value when value is included.
     *
     * @return void
     */
    public function testGetIdentifierReturnsValueWithData(): void
    {
        self::assertSame(
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

        self::assertNull($this->converter->getIdentifier($data));
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

        self::assertNull($this->converter->getIdentifier($data));
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

        self::assertNull($this->converter->getIdentifier($data));
    }

    /**
     * Assert that getDescription will return value when value is included.
     *
     * @return void
     */
    public function testGetDescriptionReturnsValueWithData(): void
    {
        self::assertSame(
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

        self::assertSame(
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

        self::assertSame(
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

        self::assertSame(
            Converter::DEFAULT_DESCRIPTION,
            $this->converter->getDescription($data)
        );
    }

    /**
     * Assert that getMinLimit will return value when value is included.
     *
     * @return void
     */
    public function testGetMinLimitReturnsValueWithData(): void
    {
        self::assertSame(
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

        self::assertSame(
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

        self::assertSame(
            Converter::DEFAULT_MIN_LIMIT,
            $this->converter->getMinLimit($data)
        );
    }

    /**
     * Assert that getMinLimit will return value when it's numeric.
     *
     * @return void
     */
    public function testGetMinLimitReturnsFloatValueWithNumeric(): void
    {
        $data = $this->apiData;
        $data[Converter::KEY_MIN_LIMIT] = '4.56';

        self::assertSame(
            4.56,
            $this->converter->getMinLimit($data)
        );
    }

    /**
     * Assert that getMaxLimit will return value when value is included.
     *
     * @return void
     */
    public function testGetMaxLimitReturnsValueWithData(): void
    {
        self::assertSame(
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

        self::assertSame(
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

        self::assertSame(
            Converter::DEFAULT_MAX_LIMIT,
            $this->converter->getMaxLimit($data)
        );
    }

    /**
     * Assert that getMaxLimit will return value when it's numeric.
     *
     * @return void
     */
    public function testGetMaxLimitReturnsFloatValueWithNumeric(): void
    {
        $data = $this->apiData;
        $data[Converter::KEY_MAX_LIMIT] = '10.12';

        self::assertSame(
            10.12,
            $this->converter->getMaxLimit($data)
        );
    }

    /**
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
     * @throws ValidatorException
     */
    public function testConvert(): void
    {
        $this->assertEquals(
            $this->modelData,
            $this->converter->convert($this->apiData)
        );
    }
}
