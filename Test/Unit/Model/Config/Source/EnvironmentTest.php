<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model\Config\Source;

use Resursbank\Core\Model\Config\Source\Environment;
use Resursbank\RBEcomPHP\ResursBank;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    /**
     * @var Environment
     */
    private Environment $environment;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->environment = new Environment();
    }

    /**
     * Assert that environment options include 'test'.
     *
     * @return void
     */
    public function testHasTestOption(): void
    {
        static::assertArrayHasKey(
            ResursBank::ENVIRONMENT_TEST,
            $this->environment->toArray()
        );
    }

    /**
     * Assert that environment options include 'production'.
     *
     * @return void
     */
    public function testHasProductionOption(): void
    {
        static::assertArrayHasKey(
            ResursBank::ENVIRONMENT_PRODUCTION,
            $this->environment->toArray()
        );
    }

    /**
     * Assert that environment has exactly two options.
     *
     * @return void
     */
    public function testHasTwoOptions(): void
    {
        static::assertCount(2, $this->environment->toArray());
    }
}
