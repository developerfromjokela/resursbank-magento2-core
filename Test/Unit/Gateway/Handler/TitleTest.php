<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Gateway\Handler;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Model\Info;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Gateway\Handler\Title;
use Resursbank\Core\Model\PaymentMethod;
use Resursbank\Core\Model\PaymentMethodRepository;
use Resursbank\Core\Gateway\Command\Gateway;

/**
 * Test cases designed for Resursbank\Core\Gateway\Handler\Title
 *
 * @package Resursbank\Core\Test\Unit\Gateway\Handler\Title
 */
class TitleTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Title
     */
    private $title;

    /**
     * @var PaymentMethodRepository
     */
    private $repo;

    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Mock payment method repository.
        $this->repo = $this->getMockBuilder(PaymentMethodRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByCode'])
            ->getMock();

        // Mock gateway object.
        $this->gateway = $this->objectManager->getObject(Gateway::class);

        // Mock Title handler.
        $this->title = $this->objectManager->getObject(
            Title::class,
            ['repository' => $this->repo, 'gateway' => $this->gateway]
        );
    }

    /**
     * Assert that the Handle method will return the default payment method
     * title if we do not provide it with any payment data at all.
     *
     * @return void
     */
    public function testHandleReturnsDefaultWithoutData(): void
    {
        $result = $this->title->handle([]);

        static::assertEquals(Title::DEFAULT_TITLE, $result);
    }

    /**
     * Assert that the Handle method will return the default payment method
     * title if we do not provide it with data but not payment method.
     *
     * @return void
     */
    public function testHandleReturnsDefaultWithoutMethod(): void
    {
        $result = $this->title->handle([
            'fake' => 'fake'
        ]);

        static::assertEquals(Title::DEFAULT_TITLE, $result);
    }

    /**
     * Assert that the Handle method will return the default payment method
     * title if we provide it with data including the payment method key which
     * contains an unexpected value (ie. not a PaymentDataObject instance).
     *
     * @return void
     */
    public function testHandleReturnsDefaultWithUnexpectedMethodData(): void
    {
        $result = $this->title->handle([
            'payment' => 123123123123
        ]);

        static::assertEquals(Title::DEFAULT_TITLE, $result);
    }

    /**
     * Assert that the Handle method will return the default title if the
     * supplied payment method doesn't have a title.
     *
     * @return void
     */
    public function testHandleReturnsDefaultWithoutPaymentTitle(): void
    {
        // Perform call.
        $result = $this->title->handle([
            'payment' => $this->getPaymentDataObject()
        ]);

        // Make sure that the Title :: handle() method will resolve the title
        // from the supplied payment method.
        static::assertEquals(Title::DEFAULT_TITLE, $result);
    }

    /**
     * Assert that the Handle method will resolve the correct title from the
     * supplied payment method model.
     *
     * @return void
     */
    public function testHandleResolvesMethodTitle(): void
    {
        // Perform call.
        $result = $this->title->handle([
            'payment' => $this->getPaymentDataObject('A handy invoice')
        ]);

        // Make sure that the Title :: handle() method will resolve the title
        // from the supplied payment method.
        static::assertEquals('A handy invoice', $result);
    }

    /**
     * @param string|null $title
     * @return object
     */
    private function getPaymentDataObject(
        ?string $title = null
    ): object {
        // Mock a PaymentMethod object.
        $method = $this->objectManager->getObject(PaymentMethod::class);

        if ($title !== null) {
            $method->setTitle($title);
        }

        // Mock a Info object.
        $info = $this->objectManager->getObject(Info::class);
        $info->setMethod('invoice');

        // Mock PaymentDataObject object.
        $methodData = $this->objectManager->getObject(
            PaymentDataObject::class,
            ['payment' => $info]
        );

        // Make getCode on PaymentMethodRepository mock result in $method.
        $this->repo->expects(static::once())
            ->method('getByCode')
            ->willReturn($method);

        return $methodData;
    }
}
