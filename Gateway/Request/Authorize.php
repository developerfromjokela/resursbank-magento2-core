<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Request;

use Resursbank\Core\Model\Api\Credentials;

/**
 * @package Resursbank\Core\Gateway\Request
 */
class Authorize extends AbstractRequest
{
    /**
     * @inheritdoc
     */
    public function logInfo(
        Credentials $credentials,
        string $reference
    ): void {
        $this->log->info(
            'Authorizing payment for order ' . $reference . ' using ' .
            $credentials->getUsername() . ' :: ' .
            $credentials->getEnvironment()
        );
    }
}
