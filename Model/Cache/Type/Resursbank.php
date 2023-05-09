<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Cache\Type;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Resursbank\Ecom\Lib\Cache\CacheInterface;

/**
 * Resursbank cache type.
 */
class Resursbank extends TagScope
{
    public const TYPE_IDENTIFIER = 'resursbank';
    public const CACHE_TAG = 'RESURSBANK';

    /**
     * @param FrontendPool $frontend
     * @param string $tag
     */
    public function __construct(
        FrontendPool $frontend,
        $tag
    ) {
        parent::__construct(
            frontend: $frontend->get(cacheType: self::TYPE_IDENTIFIER),
            tag: $tag
        );
    }
}
