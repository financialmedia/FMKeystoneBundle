<?php

namespace FM\KeystoneBundle\Cache;

interface CacheInterface
{
    /**
     * Checks if the cache has a value for a key.
     *
     * @param string $key A unique key
     *
     * @return Boolean Whether the cache has a value for this key
     */
    public function has($key);

    /**
     * Returns the value for a key.
     *
     * @param string $key A unique key
     *
     * @return string|null The value in the cache
     */
    public function get($key);

    /**
     * Sets a value in the cache.
     *
     * @param string $key   A unique key
     * @param string $value The value to cache
     * @param int    $ttl   Time-to-live
     */
    public function set($key, $value, $ttl = 0);
}
