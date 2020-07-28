<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Http\Client;

use Resursbank\Core\Model\Api\Credentials;

/**
 * Perform authorization request through Resurs Bank API.
 *
 * @package Resursbank\Core\Gateway\Http\Client
 */
class Authorize extends AbstractClient
{
    /**
     * @inheritdoc
     */
    public function execute(
        Credentials $credentials,
        string $reference
    ): array {
        return [
            'reference' => $reference,
            'status' => true
        ];
    }
}
