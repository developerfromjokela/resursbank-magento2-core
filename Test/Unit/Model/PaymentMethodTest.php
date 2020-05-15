<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Model\PaymentMethod;

/**
 * Test cases designed for PaymentMethod data model.
 *
 * @package Resursbank\Core\Test\Unit\Model
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
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

        $this->method = $objectManager->getObject(PaymentMethod::class);
    }

    /**
     * Assert that the setAccountId method will assign a value to the
     * accountId property.
     *
     * @return void
     */
    public function testSetAccountId(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::ACCOUNT_ID)
        );

        $this->method->setAccountId(123);

        static::assertSame(
            123,
            $this->method->getData(PaymentMethod::ACCOUNT_ID)
        );
    }

    /**
     * Assert that the method setAccountId will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetAccountIdReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setAccountId(123)
        );
    }

    /**
     * Assert that the method getAccountId will convert its return value to an
     * int.
     *
     * @return void
     */
    public function testGetAccountIdTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::ACCOUNT_ID, 'Test');
        static::assertSame(0, $this->method->getAccountId());
    }

    /**
     * Assert that the getAccountId method will return default value when no
     * value has been assigned to the accountId property.
     *
     * @return void
     */
    public function testGetAccountIdDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::ACCOUNT_ID, null);
        static::assertSame(321, $this->method->getAccountId(321));
        static::assertNull($this->method->getAccountId());
    }

    /**
     * Assert that the getAccountId method will return the value assigned to the
     * accountId property.
     *
     * @return void
     */
    public function testGetAccountIdExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::ACCOUNT_ID, 123);
        static::assertSame(123, $this->method->getAccountId(321));
        static::assertSame(123, $this->method->getAccountId());
    }

    /**
     * Assert that the setMethodId method will assign a value to the
     * methodId property.
     *
     * @return void
     */
    public function testSetMethodId(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::METHOD_ID)
        );

        $this->method->setMethodId(123);

        static::assertSame(
            123,
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
     * @return void
     */
    public function testSetMethodIdReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setMethodId(123)
        );
    }

    /**
     * Assert that the method getMethodId will convert its return value to an
     * int.
     *
     * @return void
     */
    public function testGetMethodIdTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::METHOD_ID, 'Test');
        static::assertSame(0, $this->method->getMethodId());
    }

    /**
     * Assert that the getMethodId method will return default value when no
     * value has been assigned to the methodId property.
     *
     * @return void
     */
    public function testGetMethodIdDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::METHOD_ID, null);
        static::assertSame(321, $this->method->getMethodId(321));
        static::assertNull($this->method->getMethodId());
    }

    /**
     * Assert that the getMethodId method will return the value assigned to the
     * methodId property.
     *
     * @return void
     */
    public function testGetMethodIdExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::METHOD_ID, 123);
        static::assertSame(123, $this->method->getMethodId(321));
        static::assertSame(123, $this->method->getMethodId());
    }

    /**
     * Assert that the method setIdentifier can assign a value to the identifier
     * property.
     *
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
     * @return void
     */
    public function testSetIdentifierReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setIdentifier('')
        );
    }

    /**
     * Assert that the getIdentifier method will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testIdentifierTypeConversionReturn(): void
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
    public function testIdentifierDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::IDENTIFIER, null);
        static::assertSame('321', $this->method->getIdentifier('321'));
        static::assertNull($this->method->getIdentifier());
    }

    /**
     * Assert that the getIdentifier method will return the value assigned to
     * the identifier property.
     *
     * @return void
     */
    public function testIdentifierExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::IDENTIFIER, '123');
        static::assertSame('123', $this->method->getIdentifier('321'));
        static::assertSame('123', $this->method->getIdentifier());
    }

    /**
     * Assert that the setCode method will assign a value to the code property.
     *
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
     * @return void
     */
    public function testSetCodeReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setCode('')
        );
    }

    /**
     * Assert that the method getCode will convert its return value to a string.
     *
     * @return void
     */
    public function testCodeTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::CODE, 123);
        static::assertSame('123', $this->method->getCode());
    }

    /**
     * Assert that the getCode method will return default value when no value
     * has been assigned to the code property.
     *
     * @return void
     */
    public function testCodeDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::CODE, null);
        static::assertSame('321', $this->method->getCode('321'));
        static::assertNull($this->method->getCode());
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
        static::assertSame('123', $this->method->getCode('321'));
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
        $this->method->setData(PaymentMethod::ACTIVE, null);
        static::assertTrue($this->method->getActive(true));
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
        static::assertTrue($this->method->getActive(false));
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

        $this->method->setTitle('Test');

        static::assertSame(
            'Test',
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
            $this->method->setTitle('')
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
        $this->method->setData(PaymentMethod::TITLE, null);
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
     * @return void
     */
    public function testSetMinOrderTotalReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setMinOrderTotal(123.123)
        );
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
        $this->method->setData(PaymentMethod::MIN_ORDER_TOTAL, null);
        static::assertSame(321.321, $this->method->getMinOrderTotal(321.321));
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
        static::assertSame(123.123, $this->method->getMinOrderTotal(321.321));
        static::assertSame(123.123, $this->method->getMinOrderTotal());
    }

    /**
     * Assert that the setMaxOrderTotal method will assign a value to the
     * maxOrderTotal property.
     *
     * @return void
     */
    public function testSetMaxOrderTotal(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::MAX_ORDER_TOTAL)
        );

        $this->method->setMaxOrderTotal(123.123);

        static::assertSame(
            123.123,
            $this->method->getData(PaymentMethod::MAX_ORDER_TOTAL)
        );
    }

    /**
     * Assert that the method setMaxOrderTotal will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetMaxOrderTotalReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setMaxOrderTotal(123.123)
        );
    }

    /**
     * Assert that the method getMaxOrderTotal will convert its return value to
     * a float.
     *
     * @return void
     */
    public function testGetMaxOrderTotalTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::MAX_ORDER_TOTAL, '123.123');
        static::assertSame(123.123, $this->method->getMaxOrderTotal());
    }

    /**
     * Assert that the getMaxOrderTotal method will return default value when no
     * value has been assigned to the maxOrderTotal property.
     *
     * @return void
     */
    public function testGetMaxOrderTotalDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::MAX_ORDER_TOTAL, null);
        static::assertSame(321.321, $this->method->getMaxOrderTotal(321.321));
        static::assertNull($this->method->getMaxOrderTotal());
    }

    /**
     * Assert that the getMaxOrderTotal method will return the value assigned to
     * the maxOrderTotal property.
     *
     * @return void
     */
    public function testGetMaxOrderTotalExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::MAX_ORDER_TOTAL, 123.123);
        static::assertSame(123.123, $this->method->getMaxOrderTotal(321.321));
        static::assertSame(123.123, $this->method->getMaxOrderTotal());
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

        $this->method->setOrderStatus('Test');

        static::assertSame(
            'Test',
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
        $this->method->setData(PaymentMethod::ORDER_STATUS, 123);
        static::assertSame('123', $this->method->getOrderStatus());
    }

    /**
     * Assert that the getOrderStatus method will return default value when no
     * value has been assigned to the orderStatus property.
     *
     * @return void
     */
    public function testOrderStatusDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::ORDER_STATUS, null);
        static::assertSame('321', $this->method->getOrderStatus('321'));
        static::assertNull($this->method->getOrderStatus());
    }

    /**
     * Assert that the getOrderStatus method will return the value assigned to
     * the orderStatus property.
     *
     * @return void
     */
    public function testOrderStatusExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::ORDER_STATUS, '123');
        static::assertSame('123', $this->method->getOrderStatus('321'));
        static::assertSame('123', $this->method->getOrderStatus());
    }

    /**
     * Assert that the setRaw method will assign a value to the raw property.
     *
     * @return void
     */
    public function testSetRaw(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::RAW)
        );

        $this->method->setRaw('Test');

        static::assertSame(
            'Test',
            $this->method->getData(PaymentMethod::RAW)
        );
    }

    /**
     * Assert that the method setRaw will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetRawReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setRaw('')
        );
    }

    /**
     * Assert that the method getRaw will convert its return value to a string.
     *
     * @return void
     */
    public function testRawTypeConversionReturn(): void
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
    public function testRawDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::RAW, null);
        static::assertSame('321', $this->method->getRaw('321'));
        static::assertNull($this->method->getRaw());
    }

    /**
     * Assert that the getRaw method will return the value assigned to the raw
     * property.
     *
     * @return void
     */
    public function testRawExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::RAW, '123');
        static::assertSame('123', $this->method->getRaw('321'));
        static::assertSame('123', $this->method->getRaw());
    }

    /**
     * Assert that the setSpecificCountry method will assign a value to the
     * specificCountry property.
     *
     * @return void
     */
    public function testSetSpecificCountry(): void
    {
        static::assertNull(
            $this->method->getData(PaymentMethod::SPECIFIC_COUNTRY)
        );

        $this->method->setSpecificCountry('Test');

        static::assertSame(
            'Test',
            $this->method->getData(PaymentMethod::SPECIFIC_COUNTRY)
        );
    }

    /**
     * Assert that the method setSpecificCountry will return an instance of the
     * PaymentMethod data model.
     *
     * @return void
     */
    public function testSetSpecificCountryReturnSelf(): void
    {
        static::assertInstanceOf(
            PaymentMethod::class,
            $this->method->setSpecificCountry('')
        );
    }

    /**
     * Assert that the method setSpecificCountry will convert its return value
     * to a string.
     *
     * @return void
     */
    public function testSpecificCountryTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::SPECIFIC_COUNTRY, 123);
        static::assertSame('123', $this->method->getSpecificCountry());
    }

    /**
     * Assert that the getSpecificCountry method will return default value when
     * no value has been assigned to the specificCountry property.
     *
     * @return void
     */
    public function testSpecificCountryDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::SPECIFIC_COUNTRY, null);
        static::assertSame('321', $this->method->getSpecificCountry('321'));
        static::assertNull($this->method->getSpecificCountry());
    }

    /**
     * Assert that the getSpecificCountry method will return the value assigned
     * to the specificCountry property.
     *
     * @return void
     */
    public function testSpecificCountryExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::SPECIFIC_COUNTRY, '123');
        static::assertSame('123', $this->method->getSpecificCountry('321'));
        static::assertSame('123', $this->method->getSpecificCountry());
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

        $this->method->setCreatedAt('Test');

        static::assertSame(
            'Test',
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
            $this->method->setCreatedAt('')
        );
    }

    /**
     * Assert that the method getCreatedAt will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testCreatedAtTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::CREATED_AT, 123);
        static::assertSame('123', $this->method->getCreatedAt());
    }

    /**
     * Assert that the getCreatedAt method will return default value when no
     * value has been assigned to the createdAt property.
     *
     * @return void
     */
    public function testCreatedAtDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::CREATED_AT, null);
        static::assertSame('321', $this->method->getCreatedAt('321'));
        static::assertNull($this->method->getCreatedAt());
    }

    /**
     * Assert that the getCreatedAt method will return the value assigned to the
     * createdAt property.
     *
     * @return void
     */
    public function testCreatedAtExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::CREATED_AT, '123');
        static::assertSame('123', $this->method->getCreatedAt('321'));
        static::assertSame('123', $this->method->getCreatedAt());
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

        $this->method->setUpdatedAt('Test');

        static::assertSame(
            'Test',
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
            $this->method->setUpdatedAt('')
        );
    }

    /**
     * Assert that the method getUpdateAt will convert its return value to a
     * string.
     *
     * @return void
     */
    public function testUpdatedAtTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::UPDATED_AT, 123);
        static::assertSame('123', $this->method->getUpdatedAt());
    }

    /**
     * Assert that the getUpdateAt method will return default value when no
     * value has been assigned to the updateAt property.
     *
     * @return void
     */
    public function testUpdatedAtDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::UPDATED_AT, null);
        static::assertSame('321', $this->method->getUpdatedAt('321'));
        static::assertNull($this->method->getUpdatedAt());
    }

    /**
     * Assert that the getUpdateAt method will return the value assigned to the
     * updateAt property.
     *
     * @return void
     */
    public function testUpdatedAtExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::UPDATED_AT, '123');
        static::assertSame('123', $this->method->getUpdatedAt('321'));
        static::assertSame('123', $this->method->getUpdatedAt());
    }
}
