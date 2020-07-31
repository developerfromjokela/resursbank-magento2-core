<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Gateway\Http\Client;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Gateway\Http\Client\Authorize;
use Resursbank\Core\Model\Api\Credentials;

/**
 * Test cases designed for Resursbank\Core\Gateway\Http\Client\Authorize
 *
 * @package Resursbank\Core\Test\Unit\Gateway\Http\Client
 */
class AuthorizeTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * @var Authorize
     */
    private $authorize;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Mock Authorize (the target of our tests).
        $this->authorize = $this->objectManager->getObject(
            Authorize::class
        );

        // Mock Credentials.
        $this->credentials = $this->objectManager->getObject(
            Credentials::class
        );
    }

    /**
     * Assert that the execute method will return the expected value.
     */
    public function testExecute(): void
    {
        try {
            $this->credentials
                ->setUsername('tester')
                ->setPassword('SomeCoolPassword123234');
        } catch (ValidatorException $e) {
            static::fail('Failed to mock credentials: ' . $e->getMessage());
        }

        $result = $this->authorize->execute(
            $this->credentials,
            '657234412567878934523'
        );

        static::assertSame([
            'reference' => '657234412567878934523',
            'status' => true
        ], $result);
    }
}
