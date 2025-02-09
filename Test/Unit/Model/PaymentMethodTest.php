<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model;

use JsonException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Model\PaymentMethod;

/**
 * Test cases designed for PaymentMethod data model.
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class PaymentMethodTest extends TestCase
{
    /**
     * @var PaymentMethod
     */
    private $method;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        /** @phpstan-ignore-next-line */
        $this->method = $objectManager->getObject(PaymentMethod::class);
    }

    /**
     * Assert that the setMethodId method will assign a value to the
     * methodId property.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetMethodId(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::METHOD_ID)
        );

        $this->method->setMethodId(1);

        self::assertSame(
            1,
            $this->method->getData(PaymentMethod::METHOD_ID)
        );

        $this->method->setMethodId(null);

        static::assertNull(
            $this->method->getData(PaymentMethod::METHOD_ID)
        );
    }

    /**
     * Assert that the method setMethodId will return an instance of the
     * PaymentMethod data model.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetMethodIdReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setMethodId(99999)
        );
    }

    /**
     * Assert that an instance of ValidatorException is thrown if the
     * setMethodId method is provided with an invalid ID.
     *
     * @return void
     */
    public function testSetMethodIdThrowsOnInvalidId(): void
    {
        $this->expectException(ValidatorException::class);
        $this->method->setMethodId(-1);
    }

    /**
     * Assert that the method getMethodId will convert its return value to an
     * int.
     *
     * @return void
     */
    public function testGetMethodIdTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::METHOD_ID, '');
        self::assertSame(0, $this->method->getMethodId());
    }

    /**
     * Assert that the getMethodId method will return default value when no
     * value has been assigned to the methodId property.
     *
     * @return void
     */
    public function testGetMethodIdDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::METHOD_ID);
        self::assertNull($this->method->getMethodId());
    }

    /**
     * Assert that the getMethodId method will return the value assigned to the
     * methodId property.
     *
     * @return void
     */
    public function testGetMethodIdExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::METHOD_ID, 111111111111);
        self::assertSame(111111111111, $this->method->getMethodId());
    }

    /**
     * Assert that the method setIdentifier can assign a value to the identifier
     * property.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetIdentifier(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::IDENTIFIER)
        );

        $this->method->setIdentifier('Test');

        static::assertSame(
            'Test',
            $this->method->getData(PaymentMethod::IDENTIFIER)
        );
    }

    /**
     * Assert that the setIdentifier method will return an instance of
     * PaymentMethod data model.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetIdentifierReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setIdentifier('INVOICE')
        );
    }

    /**
     * Assert that an instance of ValidatorException is thrown if the
     * setIdentifier method is provided with an empty string.
     *
     * @return void
     */
    public function testSetIdentifierThrowsOnEmptyString(): void
    {
        $this->expectException(ValidatorException::class);
        $this->method->setIdentifier('');
    }

    /**
     * Assert that the getIdentifier method will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testGetIdentifierTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::IDENTIFIER, 123);
        static::assertSame('123', $this->method->getIdentifier());
    }

    /**
     * Assert that the getIdentifier method will return default value when no
     * value has been assigned to the identifier property.
     *
     * @return void
     */
    public function testGetIdentifierDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::IDENTIFIER);
        self::assertNull($this->method->getIdentifier());
    }

    /**
     * Assert that the getIdentifier method will return the value assigned to
     * the identifier property.
     *
     * @return void
     */
    public function testIdentifierExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::IDENTIFIER, 'DLE');
        self::assertSame('DLE', $this->method->getIdentifier());
    }

    /**
     * Assert that the setCode method will assign a value to the code property.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetCode(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::CODE)
        );

        $this->method->setCode('Test');

        static::assertSame(
            'Test',
            $this->method->getData(PaymentMethod::CODE)
        );
    }

    /**
     * Assert that the method setCode will return an instance of the
     * PaymentMethod data model.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetCodeReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setCode('test_code')
        );
    }

    /**
     * Assert that an instance of ValidatorException is thrown if the
     * setCode method is provided with an empty string.
     *
     * @return void
     */
    public function testSetCodeThrowsOnEmptyString(): void
    {
        $this->expectException(ValidatorException::class);
        $this->method->setCode('');
    }

    /**
     * Assert that the method getCode will convert its return value to a string.
     *
     * @return void
     */
    public function testCodeTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::CODE, 'kalle_prod_part');
        self::assertSame('kalle_prod_part', $this->method->getCode());
    }

    /**
     * Assert that the getCode method will return default value when no value
     * has been assigned to the code property.
     *
     * @return void
     */
    public function testCodeDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::CODE);
        self::assertNull($this->method->getCode());
    }

    /**
     * Assert that the getCode method will return the value assigned to the
     * code property.
     *
     * @return void
     */
    public function testCodeExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::CODE, '123');
        static::assertSame('123', $this->method->getCode());
    }

    /**
     * Assert that the setActive method will assign a value to the active
     * property.
     *
     * @return void
     */
    public function testSetActive(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::ACTIVE)
        );

        $this->method->setActive(true);

        static::assertTrue(
            $this->method->getData(PaymentMethod::ACTIVE)
        );
    }

    /**
     * Assert that the method setActive will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetActiveReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setActive(true)
        );
    }

    /**
     * Assert that the method getActive will convert its return value to a
     * bool.
     *
     * @return void
     */
    public function testActiveTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::ACTIVE, 123);
        static::assertTrue($this->method->getActive());
    }

    /**
     * Assert that the getActive method will return default value when no value
     * has been assigned to the active property.
     *
     * @return void
     */
    public function testActiveDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::ACTIVE);
        static::assertNull($this->method->getActive());
    }

    /**
     * Assert that the getActive method will return the value assigned to the
     * active property.
     *
     * @return void
     */
    public function testActiveExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::ACTIVE, true);
        static::assertTrue($this->method->getActive());
    }

    /**
     * Assert that the setTitle method will assign a value to the title
     * property.
     *
     * @return void
     */
    public function testSetTitle(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::TITLE)
        );

        $this->method->setTitle('Test title');

        self::assertSame(
            'Test title',
            $this->method->getData(PaymentMethod::TITLE)
        );
    }

    /**
     * Assert that the method setTitle will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetTitleReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setTitle('Test title 1')
        );
    }

    /**
     * Assert that the method getTitle will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testTitleTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::TITLE, 123);
        static::assertSame('123', $this->method->getTitle());
    }

    /**
     * Assert that the getTitle method will return default value when no value
     * has been assigned to the title property.
     *
     * @return void
     */
    public function testTitleDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::TITLE);
        static::assertSame('321', $this->method->getTitle('321'));
        static::assertNull($this->method->getTitle());
    }

    /**
     * Assert that the getTitle method will return the value assigned to the
     * title property.
     *
     * @return void
     */
    public function testTitleExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::TITLE, '123');
        static::assertSame('123', $this->method->getTitle('321'));
        static::assertSame('123', $this->method->getTitle());
    }

    /**
     * Assert that the setMinOrderTotal method will assign a value to the
     * minOrderTotal property.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetMinOrderTotal(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::MIN_ORDER_TOTAL)
        );

        $this->method->setMinOrderTotal(123.123);

        static::assertSame(
            123.123,
            $this->method->getData(PaymentMethod::MIN_ORDER_TOTAL)
        );
    }

    /**
     * Assert that the method setMinOrderTotal will return an instance of the
     * PaymentMethod data model.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetMinOrderTotalReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setMinOrderTotal(123.8917238917)
        );
    }

    /**
     * Assert that an instance of ValidatorException is thrown if the
     * setMinOrderTotal method is provided with an invalid float.
     *
     * @return void
     */
    public function testSetMinOrderTotalThrowsOnInvalidFloat(): void
    {
        $this->expectException(ValidatorException::class);
        $this->method->setMinOrderTotal(-0.01);
    }

    /**
     * Assert that the method getMinOrderTotal will convert its return value to
     * a float.
     *
     * @return void
     */
    public function testGetMinOrderTotalTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::MIN_ORDER_TOTAL, '123.123');
        static::assertSame(123.123, $this->method->getMinOrderTotal());
    }

    /**
     * Assert that the getMinOrderTotal method will return default value when no
     * value has been assigned to the minOrderTotal property.
     *
     * @return void
     */
    public function testGetMinOrderTotalDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::MIN_ORDER_TOTAL);
        static::assertNull($this->method->getMinOrderTotal());
    }

    /**
     * Assert that the getMinOrderTotal method will return the value assigned
     * to the minOrderTotal property.
     *
     * @return void
     */
    public function testGetMinOrderTotalExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::MIN_ORDER_TOTAL, 123.123);
        self::assertSame(123.123, $this->method->getMinOrderTotal(0.1));
        self::assertSame(123.123, $this->method->getMinOrderTotal());
    }

    /**
     * Assert that the setMaxOrderTotal method will assign a value to the
     * maxOrderTotal property.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetMaxOrderTotal(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::MAX_ORDER_TOTAL)
        );

        $this->method->setMaxOrderTotal(123);

        self::assertSame(
            123.0,
            $this->method->getData(PaymentMethod::MAX_ORDER_TOTAL)
        );
    }

    /**
     * Assert that the method setMaxOrderTotal will return an instance of the
     * PaymentMethod data model.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetMaxOrderTotalReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setMaxOrderTotal(0.123)
        );
    }

    /**
     * Assert that an instance of ValidatorException is thrown if the
     * setMaxOrderTotal method is provided with an invalid float.
     *
     * @return void
     */
    public function testSetMaxOrderTotalThrowsOnInvalidFloat(): void
    {
        $this->expectException(ValidatorException::class);
        $this->method->setMaxOrderTotal(-0.01);
    }

    /**
     * Assert that the method getMaxOrderTotal will convert its return value to
     * a float.
     *
     * @return void
     */
    public function testGetMaxOrderTotalTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::MAX_ORDER_TOTAL, '12.14');
        self::assertSame(12.14, $this->method->getMaxOrderTotal());
    }

    /**
     * Assert that the getMaxOrderTotal method will return default value when no
     * value has been assigned to the maxOrderTotal property.
     *
     * @return void
     */
    public function testGetMaxOrderTotalDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::MAX_ORDER_TOTAL);
        self::assertNull($this->method->getMaxOrderTotal());
    }

    /**
     * Assert that the getMaxOrderTotal method will return the value assigned to
     * the maxOrderTotal property.
     *
     * @return void
     */
    public function testGetMaxOrderTotalExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::MAX_ORDER_TOTAL, 0.1);
        self::assertSame(0.1, $this->method->getMaxOrderTotal());
    }

    /**
     * Assert that the setOrderStatus method will assign a value to the
     * orderStatus property.
     *
     * @return void
     */
    public function testSetOrderStatus(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::ORDER_STATUS)
        );

        $this->method->setOrderStatus('ORDER_STATUS');

        self::assertSame(
            'ORDER_STATUS',
            $this->method->getData(PaymentMethod::ORDER_STATUS)
        );
    }

    /**
     * Assert that the method setOrderStatus will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetOrderStatusReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setOrderStatus('')
        );
    }

    /**
     * Assert that the method getOrderStatus will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testOrderStatusTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::ORDER_STATUS, -123);
        self::assertSame('-123', $this->method->getOrderStatus());
    }

    /**
     * Assert that the getOrderStatus method will return default value when no
     * value has been assigned to the orderStatus property.
     *
     * @return void
     */
    public function testOrderStatusDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::ORDER_STATUS);
        self::assertNull($this->method->getOrderStatus());
    }

    /**
     * Assert that the getOrderStatus method will return the value assigned to
     * the orderStatus property.
     *
     * @return void
     */
    public function testOrderStatusExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::ORDER_STATUS, 'pending_payment');
        self::assertSame('pending_payment', $this->method->getOrderStatus());
    }

    /**
     * Assert that the setRaw method will assign a value to the raw property.
     *
     * @return void
     * @throws JsonException
     */
    public function testSetRaw(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::RAW)
        );

        $data = json_encode(['test_false' => false], JSON_THROW_ON_ERROR);

        $this->method->setRaw($data);

        self::assertSame(
            $data,
            $this->method->getData(PaymentMethod::RAW)
        );
    }

    /**
     * Assert that the method setRaw will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     * @throws JsonException
     */
    public function testSetRawReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setRaw(json_encode(
                ['test' => 1],
                JSON_THROW_ON_ERROR
            ))
        );
    }

    /**
     * Assert that an instance of ValidatorException is thrown if the
     * setRaw method is provided with an invalid JSON string.
     *
     * @return void
     */
    public function testSetRawThrowsOnInvalidJson(): void
    {
        $this->expectException(JsonException::class);
        $this->method->setRaw('{test: false');
    }

    /**
     * Assert that the method getRaw will convert its return value to a string.
     *
     * @return void
     */
    public function testGetRawTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::RAW, 123);
        static::assertSame('123', $this->method->getRaw());
    }

    /**
     * Assert that the getRaw method will return default value when no value has
     * been assigned to the raw property.
     *
     * @return void
     */
    public function testGetRawDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::RAW);
        self::assertNull($this->method->getRaw());
    }

    /**
     * Assert that the getRaw method will return the value assigned to the raw
     * property.
     *
     * @return void
     */
    public function testGetRawExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::RAW, 'Testing_value');
        self::assertSame('Testing_value', $this->method->getRaw());
    }

    /**
     * Assert that the setSpecificCountry method will assign a value to the
     * specificCountry property.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetSpecificCountry(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::SPECIFIC_COUNTRY)
        );

        $this->method->setSpecificCountry('SE');

        self::assertSame(
            'SE',
            $this->method->getData(PaymentMethod::SPECIFIC_COUNTRY)
        );
    }

    /**
     * Assert that the method setSpecificCountry will return an instance of the
     * PaymentMethod data model.
     *
     * @throws ValidatorException
     * @return void
     */
    public function testSetSpecificCountryReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setSpecificCountry('NO')
        );
    }

    /**
     * Assert that an instance of ValidatorException is thrown if the
     * setSpecificCountry method is given a country ISO that is too long.
     *
     * @return void
     */
    public function testSetSpecificCountryThrowsOnTooLongIso(): void
    {
        $this->expectException(ValidatorException::class);
        $this->method->setSpecificCountry('NOT_A_COUNTRY_ISO!');
    }

    /**
     * Assert that an instance of ValidatorException is thrown if the
     * setSpecificCountry method is given an country ISO that is too short.
     *
     * @return void
     */
    public function testSetSpecificCountryThrowsOnTooShortIso(): void
    {
        $this->expectException(ValidatorException::class);
        $this->method->setSpecificCountry('N');
    }

    /**
     * Assert that an instance of ValidatorException is thrown if the
     * setSpecificCountry method is given an country ISO that has invalid
     * characters.
     *
     * @return void
     */
    public function testSetSpecificCountryThrowsOnInvalidCharacters(): void
    {
        $this->expectException(ValidatorException::class);
        $this->method->setSpecificCountry('S!');
    }

    /**
     * Assert that the method setSpecificCountry will convert its return value
     * to a string.
     *
     * @return void
     */
    public function testGetSpecificCountryTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::SPECIFIC_COUNTRY, 1.123);
        self::assertSame('1.123', $this->method->getSpecificCountry());
    }

    /**
     * Assert that the getSpecificCountry method will return default value when
     * no value has been assigned to the specificCountry property.
     *
     * @return void
     */
    public function testGetSpecificCountryDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::SPECIFIC_COUNTRY);
        self::assertNull($this->method->getSpecificCountry());
    }

    /**
     * Assert that the getSpecificCountry method will return the value assigned
     * to the specificCountry property.
     *
     * @return void
     */
    public function testGetSpecificCountryExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::SPECIFIC_COUNTRY, 'SV');
        self::assertSame('SV', $this->method->getSpecificCountry());
    }

    /**
     * Assert that the setCreatedAt method will assign a value to the
     * createdAt property.
     *
     * @return void
     */
    public function testSetCreatedAt(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::CREATED_AT)
        );

        $timestamp = time();

        $this->method->setCreatedAt($timestamp);

        self::assertSame(
            $timestamp,
            $this->method->getData(PaymentMethod::CREATED_AT)
        );
    }

    /**
     * Assert that the method setCreatedAt will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetCreatedAtReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setCreatedAt(time())
        );
    }

    /**
     * Assert that the method getCreatedAt will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testGetCreatedAtTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::CREATED_AT, '66543');
        self::assertSame(66543, $this->method->getCreatedAt());
    }

    /**
     * Assert that the getCreatedAt method will return default value when no
     * value has been assigned to the createdAt property.
     *
     * @return void
     */
    public function testGetCreatedAtDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::CREATED_AT);
        self::assertNull($this->method->getCreatedAt());
    }

    /**
     * Assert that the getCreatedAt method will return the value assigned to the
     * createdAt property.
     *
     * @return void
     */
    public function testGetCreatedAtExpectedReturn(): void
    {
        $timestamp = time();

        $this->method->setData(PaymentMethod::CREATED_AT, $timestamp);
        self::assertSame($timestamp, $this->method->getCreatedAt());
    }

    /**
     * Assert that the setUpdateAt method will assign a value to the updateAt
     * property.
     *
     * @return void
     */
    public function testSetUpdatedAt(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::UPDATED_AT)
        );

        $timestamp = time();

        $this->method->setUpdatedAt($timestamp);

        self::assertSame(
            $timestamp,
            $this->method->getData(PaymentMethod::UPDATED_AT)
        );
    }

    /**
     * Assert that the method setUpdateAt will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetUpdatedAtReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setUpdatedAt(time())
        );
    }

    /**
     * Assert that the method getUpdateAt will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testGetUpdatedAtTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::UPDATED_AT, '1234567');
        self::assertSame(1234567, $this->method->getUpdatedAt());
    }

    /**
     * Assert that the getUpdateAt method will return default value when no
     * value has been assigned to the updateAt property.
     *
     * @return void
     */
    public function testGetUpdatedAtDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::UPDATED_AT);
        self::assertNull($this->method->getUpdatedAt());
    }

    /**
     * Assert that the getUpdateAt method will return the value assigned to the
     * updateAt property.
     *
     * @return void
     */
    public function testGetUpdatedAtExpectedReturn(): void
    {
        $timestamp = time();

        $this->method->setData(PaymentMethod::UPDATED_AT, $timestamp);
        self::assertSame($timestamp, $this->method->getUpdatedAt());
    }
}
