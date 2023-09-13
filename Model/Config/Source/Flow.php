<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

class Flow extends Options
{
    /**
     * @inheritDoc
     *
     * NOTE: Options are appended through plugins in submodules adding API
     * implementations (like RCO or Simplified).
     *
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function toArray(): array
    {
        return [
        ];
    }
}
