<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin;

use Resursbank\Core\Helper\Config;
use Resursbank\Ecom\Config as EcomConfig;

/**
 * Handles initial init of Ecom+.
 */
class Init
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Perform initial setup of Ecom+
     */
    public function beforeLaunch()
    {
        EcomConfig::setup();
    }
}
