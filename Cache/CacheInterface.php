<?php

namespace Scaly\Cache;


/**
 * Interface CacheInterface
 *
 * Abstract Cache Provider
 *
 * @package Scaly\Cache
 */
interface CacheInterface
{


    /**
     * Retrieve data from cache layer
     * @param string $key Unique identifier of your data
     * @param null $default What to return if requested data is not in cache
     * @return mixed Return cached data or default
     * @throws \Scaly\Cache\Exception\InvalidArgumentException
     */
    public function get($key, $default = null);

    /**
     * Check if data is present on cache layer
     * @param string $key Unique identifier of your data
     * @return bool Returns if your data is present or not
     * @throws \Scaly\Cache\Exception\InvalidArgumentException
     */
    public function has($key);

    /**
     * Remove data from cache layer
     * @param string $key Unique identifier of your data
     * @return bool True if removal was successful else false
     * @throws \Scaly\Cache\Exception\InvalidArgumentException
     */
    public function delete($key);

    /**
     * Store data in cache layer
     * @param string $key Unique identifier of your data
     * @param mixed $data to store in cache layer
     * @return bool True if storing was successful else false
     * @throws \Scaly\Cache\Exception\InvalidArgumentException
     */
    public function set($key, $data);

}