<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Observer;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

/**
 * Cleans cache after config save.
 */
class ConfigSave implements ObserverInterface
{
    /** @var string Defines the cache type code to clean */
    private const TYPECODE = 'resursbank';

    /**
     * @param TypeListInterface $typeList
     * @param Pool $pool
     */
    public function __construct(
        private readonly TypeListInterface $typeList,
        private readonly Pool $pool
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->typeList->cleanType(typeCode: self::TYPECODE);
        foreach ($this->pool as $frontend) {
            $frontend->getBackend()->clean();
        }
    }
}
