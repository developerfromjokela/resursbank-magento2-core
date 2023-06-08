<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin;

use Resursbank\Core\Helper\Mapi;

/**
 * Handles initial init of Ecom+.
 */
class Init
{
    /**
     * @param Mapi $mapi
     */
    public function __construct(
        private readonly Mapi $mapi
    ) {
    }

    /**
     * Perform initial setup of Ecom+
     *
     * @return void
     */
    public function beforeLaunch(): void
    {
        $this->mapi->connect();
    }
}
