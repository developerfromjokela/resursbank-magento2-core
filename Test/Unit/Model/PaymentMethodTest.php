<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model;

use Magento\Framework\Exception\ValidatorException;
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
     * Assert that we can set an account id.
     */
    public function testSetAccountId(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::ACCOUNT_ID)
        );

        $this->method->setAccountId(123);

        $this->assertSame(
            123,
            $this->method->getData(PaymentMethod::ACCOUNT_ID)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetAccountIdReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setAccountId(123)
        );
    }

    /**
     * Assert that the return value is converted to an int.
     */
    public function testGetAccountIdTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::ACCOUNT_ID, 'Test');
        self::assertSame(0, $this->method->getAccountId());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testGetAccountIdDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::ACCOUNT_ID, null);
        self::assertSame(321, $this->method->getAccountId(321));
        self::assertNull($this->method->getAccountId());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testGetAccountIdExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::ACCOUNT_ID, 123);
        self::assertSame(123, $this->method->getAccountId(321));
        self::assertSame(123, $this->method->getAccountId());
    }

    /**
     * Assert that we can set a proper method id to later update a database
     * entry, and remove it so that new entries can be created.
     */
    public function testSetMethodId(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::METHOD_ID)
        );

        $this->method->setMethodId(123);

        $this->assertSame(
            123,
            $this->method->getData(PaymentMethod::METHOD_ID)
        );

        $this->method->setMethodId(null);

        $this->assertNull(
            $this->method->getData(PaymentMethod::METHOD_ID)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetMethodIdReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setMethodId(123)
        );
    }

    /**
     * Assert that the return value is converted to an int.
     */
    public function testGetMethodIdTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::METHOD_ID, 'Test');
        $this->assertSame(0, $this->method->getMethodId());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testGetMethodIdDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::METHOD_ID, null);
        $this->assertSame(321, $this->method->getMethodId(321));
        $this->assertNull($this->method->getMethodId());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testGetMethodIdExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::METHOD_ID, 123);
        $this->assertSame(123, $this->method->getMethodId(321));
        $this->assertSame(123, $this->method->getMethodId());
    }

    /**
     * Assert that we can set an identifier.
     */
    public function testSetIdentifier(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::IDENTIFIER)
        );

        $this->method->setIdentifier('Test');

        $this->assertSame(
            'Test',
            $this->method->getData(PaymentMethod::IDENTIFIER)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetIdentifierReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setIdentifier('')
        );
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testIdentifierTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::IDENTIFIER, 123);
        $this->assertSame('123', $this->method->getIdentifier());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testIdentifierDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::IDENTIFIER, null);
        $this->assertSame('321', $this->method->getIdentifier('321'));
        $this->assertNull($this->method->getIdentifier());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testIdentifierExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::IDENTIFIER, '123');
        $this->assertSame('123', $this->method->getIdentifier('321'));
        $this->assertSame('123', $this->method->getIdentifier());
    }

    /**
     * Assert that we can set a payment method code.
     */
    public function testSetCode(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::CODE)
        );

        $this->method->setCode('Test');

        $this->assertSame(
            'Test',
            $this->method->getData(PaymentMethod::CODE)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetCodeReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setCode('')
        );
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testCodeTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::CODE, 123);
        $this->assertSame('123', $this->method->getCode());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testCodeDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::CODE, null);
        $this->assertSame('321', $this->method->getCode('321'));
        $this->assertNull($this->method->getCode());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testCodeExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::CODE, '123');
        $this->assertSame('123', $this->method->getCode('321'));
        $this->assertSame('123', $this->method->getCode());
    }

    /**
     * Assert that we can set the active state of a payment method.
     */
    public function testSetActive(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::ACTIVE)
        );

        $this->method->setActive(true);

        $this->assertTrue(
            $this->method->getData(PaymentMethod::ACTIVE)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetActiveReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setActive(true)
        );
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testActiveTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::ACTIVE, 123);
        $this->assertTrue($this->method->getActive());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testActiveDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::ACTIVE, null);
        $this->assertTrue($this->method->getActive(true));
        $this->assertNull($this->method->getActive());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testActiveExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::ACTIVE, true);
        $this->assertTrue($this->method->getActive(false));
        $this->assertTrue($this->method->getActive());
    }

    /**
     * Assert that we can set a payment method title.
     */
    public function testSetTitle(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::TITLE)
        );

        $this->method->setTitle('Test');

        $this->assertSame(
            'Test',
            $this->method->getData(PaymentMethod::TITLE)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetTitleReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setTitle('')
        );
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testTitleTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::TITLE, 123);
        $this->assertSame('123', $this->method->getTitle());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testTitleDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::TITLE, null);
        $this->assertSame('321', $this->method->getTitle('321'));
        $this->assertNull($this->method->getTitle());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testTitleExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::TITLE, '123');
        $this->assertSame('123', $this->method->getTitle('321'));
        $this->assertSame('123', $this->method->getTitle());
    }

    /**
     * Assert that we can set a payment method minimum order total.
     */
    public function testSetMinOrderTotal(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::MIN_ORDER_TOTAL)
        );

        $this->method->setMinOrderTotal(123.123);

        $this->assertSame(
            123.123,
            $this->method->getData(PaymentMethod::MIN_ORDER_TOTAL)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetMinOrderTotalReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setMinOrderTotal(123.123)
        );
    }

    /**
     * Assert that the return value is converted to an int.
     */
    public function testGetMinOrderTotalTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::MIN_ORDER_TOTAL, '123.123');
        self::assertSame(123.123, $this->method->getMinOrderTotal());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testGetMinOrderTotalDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::MIN_ORDER_TOTAL, null);
        self::assertSame(321.321, $this->method->getMinOrderTotal(321.321));
        self::assertNull($this->method->getMinOrderTotal());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testGetMinOrderTotalExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::MIN_ORDER_TOTAL, 123.123);
        self::assertSame(123.123, $this->method->getMinOrderTotal(321.321));
        self::assertSame(123.123, $this->method->getMinOrderTotal());
    }

    /**
     * Assert that we can set a payment method maximum order total.
     */
    public function testSetMaxOrderTotal(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::MAX_ORDER_TOTAL)
        );

        $this->method->setMaxOrderTotal(123.123);

        $this->assertSame(
            123.123,
            $this->method->getData(PaymentMethod::MAX_ORDER_TOTAL)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetMaxOrderTotalReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setMaxOrderTotal(123.123)
        );
    }

    /**
     * Assert that the return value is converted to an int.
     */
    public function testGetMaxOrderTotalTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::MAX_ORDER_TOTAL, '123.123');
        self::assertSame(123.123, $this->method->getMaxOrderTotal());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testGetMaxOrderTotalDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::MAX_ORDER_TOTAL, null);
        self::assertSame(321.321, $this->method->getMaxOrderTotal(321.321));
        self::assertNull($this->method->getMaxOrderTotal());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testGetMaxOrderTotalExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::MAX_ORDER_TOTAL, 123.123);
        self::assertSame(123.123, $this->method->getMaxOrderTotal(321.321));
        self::assertSame(123.123, $this->method->getMaxOrderTotal());
    }

    /**
     * Assert that we can set a payment method's order status.
     */
    public function testSetOrderStatus(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::ORDER_STATUS)
        );

        $this->method->setOrderStatus('Test');

        $this->assertSame(
            'Test',
            $this->method->getData(PaymentMethod::ORDER_STATUS)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetOrderStatusReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setOrderStatus('')
        );
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testOrderStatusTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::ORDER_STATUS, 123);
        $this->assertSame('123', $this->method->getOrderStatus());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testOrderStatusDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::ORDER_STATUS, null);
        $this->assertSame('321', $this->method->getOrderStatus('321'));
        $this->assertNull($this->method->getOrderStatus());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testOrderStatusExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::ORDER_STATUS, '123');
        $this->assertSame('123', $this->method->getOrderStatus('321'));
        $this->assertSame('123', $this->method->getOrderStatus());
    }

    /**
     * Assert that we can set a payment method's raw value.
     */
    public function testSetRaw(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::RAW)
        );

        $this->method->setRaw('Test');

        $this->assertSame(
            'Test',
            $this->method->getData(PaymentMethod::RAW)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetRawReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setRaw('')
        );
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testRawTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::RAW, 123);
        $this->assertSame('123', $this->method->getRaw());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testRawDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::RAW, null);
        $this->assertSame('321', $this->method->getRaw('321'));
        $this->assertNull($this->method->getRaw());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testRawExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::RAW, '123');
        $this->assertSame('123', $this->method->getRaw('321'));
        $this->assertSame('123', $this->method->getRaw());
    }

    /**
     * Assert that we can set a payment method's associated country.
     */
    public function testSetSpecificCountry(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::SPECIFIC_COUNTRY)
        );

        $this->method->setSpecificCountry('Test');

        $this->assertSame(
            'Test',
            $this->method->getData(PaymentMethod::SPECIFIC_COUNTRY)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetSpecificCountryReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setSpecificCountry('')
        );
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testSpecificCountryTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::SPECIFIC_COUNTRY, 123);
        $this->assertSame('123', $this->method->getSpecificCountry());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testSpecificCountryDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::SPECIFIC_COUNTRY, null);
        $this->assertSame('321', $this->method->getSpecificCountry('321'));
        $this->assertNull($this->method->getSpecificCountry());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testSpecificCountryExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::SPECIFIC_COUNTRY, '123');
        $this->assertSame('123', $this->method->getSpecificCountry('321'));
        $this->assertSame('123', $this->method->getSpecificCountry());
    }

    /**
     * Assert that we can set a payment method's created at timestamp.
     */
    public function testSetCreatedAt(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::CREATED_AT)
        );

        $this->method->setCreatedAt('Test');

        $this->assertSame(
            'Test',
            $this->method->getData(PaymentMethod::CREATED_AT)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetCreatedAtReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setCreatedAt('')
        );
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testCreatedAtTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::CREATED_AT, 123);
        $this->assertSame('123', $this->method->getCreatedAt());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testCreatedAtDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::CREATED_AT, null);
        $this->assertSame('321', $this->method->getCreatedAt('321'));
        $this->assertNull($this->method->getCreatedAt());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testCreatedAtExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::CREATED_AT, '123');
        $this->assertSame('123', $this->method->getCreatedAt('321'));
        $this->assertSame('123', $this->method->getCreatedAt());
    }

    /**
     * Assert that we can set a payment method's created at timestamp.
     */
    public function testSetUpdatedAt(): void
    {
        $this->assertNull(
            $this->method->getData(PaymentMethod::UPDATED_AT)
        );

        $this->method->setUpdatedAt('Test');

        $this->assertSame(
            'Test',
            $this->method->getData(PaymentMethod::UPDATED_AT)
        );
    }

    /**
     * Assert that the return value is the instance itself.
     */
    public function testSetUpdatedAtReturnSelf(): void
    {
        $this->assertInstanceOf(
            PaymentMethod::class,
            $this->method->setUpdatedAt('')
        );
    }

    /**
     * Assert that the return value is converted to a string.
     */
    public function testUpdatedAtTypeConversionReturn(): void
    {
        $this->method->setData(PaymentMethod::UPDATED_AT, 123);
        $this->assertSame('123', $this->method->getUpdatedAt());
    }

    /**
     * Assert that the we can specify and return a default value in the case
     * where a value hasn't been set.
     */
    public function testUpdatedAtDefaultReturn(): void
    {
        $this->method->setData(PaymentMethod::UPDATED_AT, null);
        $this->assertSame('321', $this->method->getUpdatedAt('321'));
        $this->assertNull($this->method->getUpdatedAt());
    }

    /**
     * Assert that if a value has been set, we get back the expected value.
     */
    public function testUpdatedAtExpectedReturn(): void
    {
        $this->method->setData(PaymentMethod::UPDATED_AT, '123');
        $this->assertSame('123', $this->method->getUpdatedAt('321'));
        $this->assertSame('123', $this->method->getUpdatedAt());
    }
}
