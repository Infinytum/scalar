<?php

namespace Scalar\Cache\Storage;

use Scalar\Cache\Exception\CacheStorageException;

/**
 * Interface CacheStorageInterface
 *
 * Persistence Providers for caching
 *
 * @package Scalar\Cache\Storage
 */
interface CacheStorageInterface
{

    /**
     * Check if this storage is available
     *
     * @return bool
     */
    public static function isAvailable();

    /**
     * Store data in persistence layer of cache
     *
     * @param string $key Unique identifier of your data
     * @param mixed $data Data to store
     * @return bool True if storing succeeded
     * @throws CacheStorageException
     */
    public function store($key, $data);

    /**
     * Retrieve data from cache
     *
     * @param string $key Unique identifier of your data
     * @return mixed Stored data from persistence
     * @throws CacheStorageException
     */
    public function retrieve($key);

    /**
     * Delete data from cache
     *
     * @param string $key Unique identifier of your data
     * @return bool True if removal was successful
     * @throws CacheStorageException
     */
    public function delete($key);

    /**
     * Check if data exists
     *
     * @param string $key Unique identifier of your data
     * @return bool True if key was found
     * @throws CacheStorageException
     */
    public function check($key);

    /**
     * Delete all data stored in cache
     *
     * @throws CacheStorageException
     */
    public function clear();

}