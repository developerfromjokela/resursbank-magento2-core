<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Magento\Framework\Phrase;
use Resursbank\Ecom\Lib\Log\LogLevel as EcomLogLevel;

/**
 * Compile list of available log levels.
 */
class LogLevel extends Options
{
    /**
     * @inheritDoc
     * @return array<int, Phrase>
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function toArray(): array
    {
        $options = [];

        foreach (EcomLogLevel::cases() as $case) {
            $options[$case->value] = $case->name;
        }

        return $options;
    }
}
