<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source\CleanOrders;

use Magento\Framework\Phrase;
use Resursbank\Core\Model\Config\Source\Options;

/**
 * Gives a list of available minimum ages for old orders.
 */
class MinimumAge extends Options
{
    /**
     * @inheritDoc
     * @return array<int, Phrase>
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function toArray(): array
    {
        $options = [];

        $options[6] = 6;
        $options[12] = 12;
        $options[24] = 24;
        $options[36] = 36;
        $options[48] = 48;
        $options[72] = 72;

        return $options;
    }
}
