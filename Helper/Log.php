<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

class Log extends AbstractLog
{
    /**
     * @inheritDoc
     */
    protected string $loggerName = 'Resursbank Core Log';

    /**
     * @inheritDoc
     */
    protected string $file = 'resursbank_core';
}
