<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper;

use Exception;
use JsonException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\Model\Api\Credentials as CredentialsModel;
use Resursbank\Core\Model\PaymentMethod as PaymentMethodModel;
use Resursbank\Core\Model\Payment\Resursbank as Method;
use Resursbank\RBEcomPHP\RESURS_ENVIRONMENTS;
use Resursbank\RBEcomPHP\ResursBank;

/**
 * Test cases designed for Resursbank\Core\Helper\PaymentMethods
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class PaymentMethodsTest extends TestCase
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
     * @var ResursBank
     */
    private $connection;

    /**
     * @var CredentialsModel
     */
    private $credentials;

    /**
     * @var array
     */
    private $convertedMethodData;

    /**
     * @var PaymentMethods
     */
    private $paymentMethods;

    /**
     * @var Credentials
     */
    private $credentialsHelper;

    /**
     * @inheritDoc
     * @throws JsonException
     */
    protected function setUp(): void
    {
        // Prepare object manager.
        $this->objectManager = new ObjectManager($this);

        // Setup mock of Payment Method data as it would appearing after being
        // converted from an API call.
        $this->convertedMethodData = [
            PaymentMethodInterface::IDENTIFIER => 'invoice',
            PaymentMethodInterface::TITLE => 'Invoice Nr. 1',
            PaymentMethodInterface::MIN_ORDER_TOTAL => 10,
            PaymentMethodInterface::MAX_ORDER_TOTAL => 50000,
            PaymentMethodInterface::RAW => json_encode(
                ['array', 'with', 'lots', 'of', 'data'],
                JSON_THROW_ON_ERROR
            )
        ];

        // Mock the API service class so we can later modify the behaviour of
        // the getConnection method.
        $this->api = $this->getMockBuilder(Api::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMock();

        // Mock instance of ResursBank class (API connection) so we can later
        // modify the behaviour of the getPaymentMethods method.
        $this->connection = $this->getMockBuilder(ResursBank::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPaymentMethods'])
            ->getMock();

        // Mock the Credentials service class so we can later modify the
        // behaviour of the getMethodSuffix and getCountry methods.
        $this->credentialsHelper = $this->getMockBuilder(Credentials::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodSuffix', 'getCountry'])
            ->getMock();

        // Create mocked, empty, instance of Credentials model.
        $this->credentials = $this->objectManager
            ->getObject(CredentialsModel::class);

        // Mock of PaymentMethods service class.
        $this->paymentMethods = $this->objectManager->getObject(
            PaymentMethods::class,
            [
                'api' => $this->api,
                'credentials' => $this->credentialsHelper
            ]
        );
    }

    /**
     * Assert that the fetch method throws an instance of IntegrationException
     * when an Exception occurs while attempting to fetch a list of available
     * payment methods from the API. Also ensure that the original Exception
     * message from Resurs Bank is forwarded unmodified.
     *
     * @return void
     */
    public function testFetchThrowsIntegrationExceptionOnFailure(): void
    {
        $this->expectException(IntegrationException::class);
        $this->expectExceptionMessage('Some connection error.');

        // Make the getConnection method on our API adapter toss an Exception.
        $this->api->expects(static::once())
            ->method('getConnection')
            ->will(static::throwException(
                new Exception('Some connection error.')
            ));

        $this->paymentMethods->fetch($this->credentials);
    }

    /**
     * Assert that the fetch method throws an instance of IntegrationException
     * when ECom relies anything that isn't an array from an API call to fetch a
     * list of available payment methods.
     *
     * @return void
     */
    public function testFetchThrowsIntegrationExceptionOnInaccurateData(): void
    {
        $this->expectException(IntegrationException::class);
        $this->expectExceptionMessage(
            'Failed to fetch payment methods from API. Expected Array.'
        );

        // Modify return value of getPaymentMethods method from the API class.
        $this->connection->method('getPaymentMethods')
            ->willReturn('This is not an array.');

        // Make sure our API adapter returns our mocked API class instance.
        $this->api->expects(static::once())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->paymentMethods->fetch($this->credentials);
    }

    /**
     * Assert that the fetch method returns the value from a getPaymentMethods
     * API call, unmodified.
     *
     * @return void
     */
    public function testFetchReturnValue(): void
    {
        // Expected data returned from the API when fetching payment methods.
        $methodsData = [
            (object) [
                'identifier' => 'invoice',
                'description' => 'test',
                'minLimit' => 5.0,
                'maxLimit' => 10.0
            ],
            (object) [
                'identifier' => 'partpayment',
                'description' => 'testing_more',
                'minLimit' => 100.0,
                'maxLimit' => 50000.0
            ]
        ];

        // Modify return value of getPaymentMethods method from the API class.
        $this->connection->expects(static::once())
            ->method('getPaymentMethods')
            ->willReturn(
                $methodsData
            );

        // Make sure our API adapter returns our mocked API class instance.
        $this->api->expects(static::once())
            ->method('getConnection')
            ->willReturn($this->connection);

        /** @var PaymentMethods $methods */
        $methods = $this->objectManager
            ->getObject(PaymentMethods::class, ['api' => $this->api]);

        try {
            // Assert our fetch method does not alter the data retrieved through
            // the API adapter.
            static::assertSame($methodsData, $methods->fetch(
                $this->credentials
            ));
        } catch (IntegrationException $e) {
            static::fail(
                'Failed asserting return value of the fetch method: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that the getCode method will return a string matching the pattern
     * [resursbank_][invoice][_][myusername_1]
     */
    public function testGetCode(): void
    {
        $this->credentialsHelper
            ->expects(static::once())
            ->method('getMethodSuffix')
            ->willReturn('batman_' . RESURS_ENVIRONMENTS::TEST);

        try {
            static::assertSame(
                (
                    Method::CODE_PREFIX .
                    'invoice_' .
                    'batman_' .
                    RESURS_ENVIRONMENTS::TEST
                ),
                $this->paymentMethods->getCode('invoice', $this->credentials)
            );
        } catch (ValidatorException $e) {
            static::fail(
                'Unexpected ValidatorException while resolving method code: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that the getCode method will convert method identifier to
     * lowercase.
     */
    public function testGetCodeWillLowercaseMethodIdentifier(): void
    {
        $this->credentialsHelper
            ->expects(static::once())
            ->method('getMethodSuffix')
            ->willReturn('tony_' . RESURS_ENVIRONMENTS::TEST);

        try {
            static::assertSame(
                (
                    Method::CODE_PREFIX .
                    'partpay_' .
                    'tony_' .
                    RESURS_ENVIRONMENTS::TEST
                ),
                $this->paymentMethods->getCode('PartPAY', $this->credentials)
            );
        } catch (ValidatorException $e) {
            static::fail(
                'Unexpected ValidatorException while resolving method code: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that an instance of ValidatorException is thrown when we attempt
     * to generate a payment method code without an identifier value.
     */
    public function testGetCodeThrowsWithEmptyIdentifier(): void
    {
        $this->expectException(ValidatorException::class);

        $this->paymentMethods->getCode('', $this->credentials);
    }

    /**
     * Assert that an instance of ValidatorException is thrown when we attempt
     * to validate converted method data where the identifier index is absent.
     */
    public function testValidateDataThrowsWithoutIdentifier(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing identifier index.');

        unset($this->convertedMethodData[PaymentMethodInterface::IDENTIFIER]);

        try {
            $this->getValidateDataMethod($this->paymentMethods)->invoke(
                $this->paymentMethods,
                $this->convertedMethodData
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate method reflection of validateData: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that an instance of ValidatorException is thrown when we attempt
     * to validate converted method data where the min_order_total index is
     * absent.
     */
    public function testValidateDataThrowsWithoutMinOrderTotal(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing min_order_total index.');

        unset(
            $this->convertedMethodData[PaymentMethodInterface::MIN_ORDER_TOTAL]
        );

        try {
            $this->getValidateDataMethod($this->paymentMethods)->invoke(
                $this->paymentMethods,
                $this->convertedMethodData
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate method reflection of validateData: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that an instance of ValidatorException is thrown when we attempt
     * to validate converted method data where the max_order_total index is
     * absent.
     */
    public function testValidateDataThrowsWithoutMaxOrderTotal(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing max_order_total index.');

        unset(
            $this->convertedMethodData[PaymentMethodInterface::MAX_ORDER_TOTAL]
        );

        try {
            $this->getValidateDataMethod($this->paymentMethods)->invoke(
                $this->paymentMethods,
                $this->convertedMethodData
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate method reflection of validateData: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that an instance of ValidatorException is thrown when we attempt
     * to validate converted method data where the title index is absent.
     */
    public function testValidateDataThrowsWithoutTitle(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing title index.');

        unset($this->convertedMethodData[PaymentMethodInterface::TITLE]);

        try {
            $this->getValidateDataMethod($this->paymentMethods)->invoke(
                $this->paymentMethods,
                $this->convertedMethodData
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate method reflection of validateData: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that an instance of ValidatorException is thrown when we attempt
     * to validate converted method data where the raw index is absent.
     */
    public function testValidateDataThrowsWithoutRaw(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Missing raw index.');

        unset($this->convertedMethodData[PaymentMethodInterface::RAW]);

        try {
            $this->getValidateDataMethod($this->paymentMethods)->invoke(
                $this->paymentMethods,
                $this->convertedMethodData
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate method reflection of validateData: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that no Exception will be thrown if all the required data is
     * present in the array submitted to the validateData method.
     *
     * NOTE: ValidatorException will be thrown if the data supplied to the
     * validation method is inaccurate.
     *
     * @doesNotPerformAssertions
     */
    public function testValidateData(): void
    {
        try {
            $this->getValidateDataMethod($this->paymentMethods)->invoke(
                $this->paymentMethods,
                $this->convertedMethodData
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate reflection of validateData method: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that the resolveMethodDataArray method accepts an instance of
     * stdClass (the expected, though not defined, return value from ECom when
     * fetching payment methods).
     *
     * NOTE: The expectation is that no Exception is thrown when the method
     * executes, thus the doesNotPerformAssertions annotation.
     *
     * @doesNotPerformAssertions
     */
    public function testResolveMethodDataArrayAcceptsStdClass(): void
    {
        try {
            $this->getResolveMethodDataArrayMethod(
                $this->paymentMethods
            )->invoke(
                $this->paymentMethods,
                (object)$this->convertedMethodData
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate reflection of validateData method: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that the resolveMethodDataArray method accepts an array.
     *
     * NOTE: The expectation is that no Exception is thrown when the method
     * executes, thus the doesNotPerformAssertions annotation.
     *
     * @doesNotPerformAssertions
     */
    public function testResolveMethodDataArrayAcceptsArray(): void
    {
        try {
            $this->getResolveMethodDataArrayMethod(
                $this->paymentMethods
            )->invoke(
                $this->paymentMethods,
                $this->convertedMethodData
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate reflection of validateData method: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that the resolveMethodDataArray method converts an instance of
     * stdClass to a simple array.
     */
    public function testResolveMethodDataArrayConversion(): void
    {
        try {
            static::assertSame(
                $this->convertedMethodData,
                $this->getResolveMethodDataArrayMethod(
                    $this->paymentMethods
                )->invoke(
                    $this->paymentMethods,
                    (object)$this->convertedMethodData
                )
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate reflection of validateData method: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Assert that the resolveMethodDataArray method will throw an instance of
     * IntegrationException when passed an integer.
     */
    public function testResolveMethodDataArrayDeclinesInt(): void
    {
        try {
            $this->expectException(IntegrationException::class);

            $this->getResolveMethodDataArrayMethod(
                $this->paymentMethods
            )->invoke(
                $this->paymentMethods,
                5
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate method reflection of ' .
                'resolveMethodDataArray: ' . $e->getMessage()
            );
        }
    }

    /**
     * Assert that the resolveMethodDataArray method will throw an instance of
     * IntegrationException when passed a float.
     */
    public function testResolveMethodDataArrayDeclinesFloat(): void
    {
        try {
            $this->expectException(IntegrationException::class);

            $this->getResolveMethodDataArrayMethod(
                $this->paymentMethods
            )->invoke(
                $this->paymentMethods,
                786.33
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate method reflection of ' .
                'resolveMethodDataArray: ' . $e->getMessage()
            );
        }
    }

    /**
     * Assert that the resolveMethodDataArray method will throw an instance of
     * IntegrationException when passed a bool.
     */
    public function testResolveMethodDataArrayDeclinesBool(): void
    {
        try {
            $this->expectException(IntegrationException::class);

            $this->getResolveMethodDataArrayMethod(
                $this->paymentMethods
            )->invoke(
                $this->paymentMethods,
                true
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate method reflection of ' .
                'resolveMethodDataArray: ' . $e->getMessage()
            );
        }
    }

    /**
     * Assert that the resolveMethodDataArray method will throw an instance of
     * IntegrationException when passed null.
     */
    public function testResolveMethodDataArrayDeclinesNull(): void
    {
        try {
            $this->expectException(IntegrationException::class);

            $this->getResolveMethodDataArrayMethod(
                $this->paymentMethods
            )->invoke(
                $this->paymentMethods,
                null
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate method reflection of ' .
                'resolveMethodDataArray: ' . $e->getMessage()
            );
        }
    }

    /**
     * Assert that the fill method works by comparing the value of each assigned
     * property on the resulting data model to the data supplied to the method.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFill(): void
    {
        $this->credentialsHelper
            ->expects(static::once())
            ->method('getCountry')
            ->willReturn('SE');

        $this->credentialsHelper
            ->expects(static::once())
            ->method('getMethodSuffix')
            ->willReturn('cassandra_' . RESURS_ENVIRONMENTS::TEST);

        /** @var PaymentMethodModel $method */
        $method = $this->objectManager->getObject(
            PaymentMethodModel::class
        );

        try {
            // Modify return value of a number of methods involved in the
            // process which fills an instance of the Method data model.
            $this->credentials
                ->setEnvironment(RESURS_ENVIRONMENTS::TEST)
                ->setUsername('Montana')
                ->setPassword('dneirfelttilymotollehyas');

            // Fill method model instance with data.
            $this->getFillMethod($this->paymentMethods)->invoke(
                $this->paymentMethods,
                $method,
                $this->convertedMethodData,
                $this->credentials
            );
        } catch (ValidatorException $e) {
            static::fail(
                'Unexpected ValidatorException: ' . $e->getMessage()
            );
        } catch (ReflectionException $e) {
            static::fail(
                'Failed to initiate reflection of fill method: ' .
                $e->getMessage()
            );
        }

        // Assert property identifier was assigned the expected value.
        static::assertSame(
            $this->convertedMethodData[PaymentMethodInterface::IDENTIFIER],
            $method->getIdentifier(),
        );

        // Assert property title was assigned the expected value.
        static::assertSame(
            $this->convertedMethodData[PaymentMethodInterface::TITLE],
            $method->getTitle(),
        );

        // Assert property min_order_total was assigned the expected value.
        static::assertSame(
            (float) $this->convertedMethodData[
            PaymentMethodInterface::MIN_ORDER_TOTAL
            ],
            $method->getMinOrderTotal(),
        );

        // Assert property max_order_total was assigned the expected value.
        static::assertSame(
            (float) $this->convertedMethodData[
            PaymentMethodInterface::MAX_ORDER_TOTAL
            ],
            $method->getMaxOrderTotal(),
        );

        // Assert property raw was assigned the expected value.
        static::assertSame(
            $this->convertedMethodData[PaymentMethodInterface::RAW],
            $method->getRaw(),
        );

        // Assert property code was assigned the expected value.
        static::assertSame(
            (
                Method::CODE_PREFIX .
                $this->convertedMethodData[
                PaymentMethodInterface::IDENTIFIER
                ] .
                '_cassandra_' .
                RESURS_ENVIRONMENTS::TEST
            ),
            $method->getCode()
        );

        // Assert property active was assigned the expected value.
        static::assertTrue($method->getActive());

        // Assert property specific_country was assigned the expected value.
        static::assertSame(
            'SE',
            $method->getSpecificCountry()
        );
    }

    /**
     * Retrieve accessible validateData method mock.
     *
     * @param PaymentMethods $obj
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    private function getValidateDataMethod(
        PaymentMethods $obj
    ): ReflectionMethod {
        $obj = new ReflectionObject($obj);
        $method = $obj->getMethod('validateData');
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Retrieve accessible resolveMethodDataArray method mock.
     *
     * @param PaymentMethods $obj
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    private function getResolveMethodDataArrayMethod(
        PaymentMethods $obj
    ): ReflectionMethod {
        $obj = new ReflectionObject($obj);
        $method = $obj->getMethod('resolveMethodDataArray');
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Retrieve accessible fill method mock.
     *
     * @param PaymentMethods $obj
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    private function getFillMethod(
        PaymentMethods $obj
    ): ReflectionMethod {
        $obj = new ReflectionObject($obj);
        $method = $obj->getMethod('fill');
        $method->setAccessible(true);

        return $method;
    }
}
