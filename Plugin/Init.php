<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin;

use Resursbank\Core\Helper\Ecom;

/**
 * Handles initial init of Ecom+.
 */
class Init
{
    /**
     * @param Ecom $ecom
     */
    public function __construct(
        private readonly Ecom $ecom
    ) {
    }

    /**
     * Perform initial setup of Ecom+
     *
     * @return void
     */
    public function beforeLaunch(): void
    {
        $this->ecom->connect();
    }
}
