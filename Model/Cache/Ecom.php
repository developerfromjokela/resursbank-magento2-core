<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Cache;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\App\CacheInterface as MagentoCacheInterface;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\Serialize\SerializerInterface;
use Resursbank\Core\Model\Cache\Type\Resursbank;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Resursbank\Ecom\Lib\Cache\CacheInterface;

use function is_string;

/**
 * Ecom cache adapter, utilising custom cache type Resursbank (Type\Resursbank).
 */
class Ecom extends AbstractCache implements CacheInterface
{
    /**
     * @param MagentoCacheInterface $cache
     */
    public function __construct(
        private readonly MagentoCacheInterface $cache
    ) {
    }

    /**
     * Read data from Magento cache.
     *
     * @param string $key
     * @return string|null
     * @throws ValidationException
     */
    public function read(string $key): ?string
    {
        $this->validateKey(key: $key);

        $result = $this->cache->load(identifier: $key);

        if (!is_string(value: $result)) {
            $result = null;
        }

        return $result;
    }

    /**
     * Write data to Magento cache.
     *
     * @param string $key
     * @param string $data
     * @param int $ttl
     * @return void
     * @throws ValidationException
     */
    public function write(string $key, string $data, int $ttl): void
    {
        $this->validateKey(key: $key);

        $this->cache->save(
            data: $data,
            identifier: $key,
            tags: [Resursbank::CACHE_TAG],
            lifeTime: $ttl
        );
    }

    /**
     * Remove data from Magento cache.
     *
     * @param string $key
     * @return void
     * @throws ValidationException
     */
    public function clear(string $key): void
    {
        $this->validateKey(key: $key);

        $this->cache->remove(identifier: $key);
    }

    /**
     * Invalidate data in Magento cache.
     *
     * @return void
     * @throws ConfigException
     */
    public function invalidate(): void
    {
        $this->cache->clean(tags: [Resursbank::CACHE_TAG]);

        parent::invalidate();
    }
}
