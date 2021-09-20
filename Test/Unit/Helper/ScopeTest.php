<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Scope;

class ScopeTest extends TestCase
{

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Scope
     */
    private Scope $scope;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->scope = new Scope(
            $contextMock,
            $this->requestMock
        );
    }

    /**
     * Assert that getTypeParam returns Website value if website param is set
     */
    public function testGetTypeParamReturnsWebsite()
    {
        $this->requestMock->expects(self::once())
            ->method('getParam')
            ->with('website')
            ->willReturn(1);
        self::assertEquals(ScopeInterface::SCOPE_WEBSITE, $this->scope->getTypeParam());
    }

    /**
     * Assert that getTypeParam returns store value if store param is set
     */
    public function testGetTypeParamReturnsStore()
    {
        $this->requestMock->expects(self::exactly(2))
            ->method('getParam')
            ->withConsecutive(['website'], ['store'])
            ->willReturn(null, 1);
        self::assertEquals(ScopeInterface::SCOPE_STORE, $this->scope->getTypeParam());
    }

    /**
     * Assert that getTypeParam returns empty value if neither website nor store param is set
     */
    public function testGetTypeParamReturnsEmptyValue()
    {
        $this->requestMock->expects(self::exactly(2))
            ->method('getParam')
            ->withConsecutive(['website'], ['store'])
            ->willReturn(null, null);
        self::assertEquals('', $this->scope->getTypeParam());
    }

    /**
     * Assert that getType returns Website value if website param is set
     */
    public function testGetTypeReturnsWebsite()
    {
        $this->requestMock->expects(self::once())
            ->method('getParam')
            ->with('website')
            ->willReturn(1);
        self::assertEquals(ScopeInterface::SCOPE_WEBSITES, $this->scope->getType());
    }

    /**
     * Assert that getType returns store value if store param is set
     */
    public function testGetTypeReturnsStore()
    {
        $this->requestMock->expects(self::exactly(2))
            ->method('getParam')
            ->withConsecutive(['website'], ['store'])
            ->willReturn(null, 1);
        self::assertEquals(ScopeInterface::SCOPE_STORES, $this->scope->getType());
    }

    /**
     * Assert that getTypeParam returns default value if neither website nor store param is set
     */
    public function testGetTypeReturnsDefaultValue()
    {
        $this->requestMock->expects(self::exactly(2))
            ->method('getParam')
            ->withConsecutive(['website'], ['store'])
            ->willReturn(null, null);
        self::assertEquals(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $this->scope->getType());
    }

    /**
     * Assert that the value returned from getId is a string
     */
    public function testGetIdReturnsStringOfNumericParam()
    {
        $this->requestMock->expects(self::exactly(2))
            ->method('getParam')
            ->withConsecutive(['website'], ['website'])
            ->willReturn(1, 1);

        self::assertIsString($this->scope->getId());
    }

    /**
     * Assert that the value returned from getId is correct
     */
    public function testGetIdReturnsCorrectValue()
    {
        $this->requestMock->expects(self::exactly(2))
            ->method('getParam')
            ->withConsecutive(['website'], ['website'])
            ->willReturn("2", "2");

        self::assertEquals("2",$this->scope->getId());
    }

    /**
     * Assert that the value returned from getId is null if no params is set
     */
    public function testGetIdReturnsNull()
    {
        $this->requestMock->expects(self::exactly(2))
            ->method('getParam')
            ->withConsecutive(['website'], ['store'])
            ->willReturn(null, null);

        self::assertNull($this->scope->getId());
    }
}
