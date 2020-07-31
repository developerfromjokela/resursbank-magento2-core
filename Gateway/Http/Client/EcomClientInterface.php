<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Http\Client;

use Resursbank\Core\Model\Api\Credentials;

/**
 * Describes methods required to perform outgoing API requests to the Resurs
 * Bank API.
 *
 * @package Resursbank\Core\Gateway\Http
 */
interface EcomClientInterface
{
    /**
     * Execute Resurs Bank API request through ECom API adapter.
     *
     * @param Credentials $credentials
     * @param string $reference
     * @return array
     */
    public function execute(
        Credentials $credentials,
        string $reference
    ): array;
}
