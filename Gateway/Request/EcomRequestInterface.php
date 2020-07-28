<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Request;

use Resursbank\Core\Model\Api\Credentials;

/**
 * Describes methods required to collect and construct request data.
 *
 * @package Resursbank\Core\Gateway\Request
 */
interface EcomRequestInterface
{
    /**
     * Execute Resurs Bank API request through ECom API adapter.
     *
     * @param Credentials $credentials
     * @param string $reference
     * @return void
     */
    public function logInfo(
        Credentials $credentials,
        string $reference
    ): void;
}
