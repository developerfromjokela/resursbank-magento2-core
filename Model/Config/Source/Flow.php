<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Magento\Framework\Phrase;

class Flow extends Options
{
    /**
     * @inheritDoc
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'rco' => 'Resurs bank Checkout',
            'simplified' => 'Resurs bank Simplified'
        ];
    }
}
