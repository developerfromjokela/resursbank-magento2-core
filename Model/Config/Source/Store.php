<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Config\Source;

use Magento\Framework\Phrase;
use Resursbank\Core\Helper\Log;
use Resursbank\Ecom\Module\Store\Repository;
use Resursbank\RBEcomPHP\ResursBank;
use Throwable;

/**
 * List of available stores for configured API account (MAPI).
 */
class Store extends Options
{
    /**
     * @param Log $log
     */
    public function __construct(
        private readonly Log $log
    ) {
    }

    /**
     * @inheritDoc
     * @return array<int, Phrase>
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function toArray(): array
    {
        try {
            return Repository::getStores()->getSelectList();
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return [];
    }
}
