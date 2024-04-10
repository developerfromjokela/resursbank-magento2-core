<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Magento\Framework\Phrase;

/**
 * List of available stores for configured API account.
 */
class Store extends Options
{
    /**
     * @inheritDoc
     * @return array<int, Phrase>
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function toArray(): array
    {
        return [];
    }
}
