<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 08.05.17
 * Time: 21:02
 */

namespace Scaly\Cache;


use Scaly\Cache\Exception\CacheStorageException;
use Scaly\Cache\Exception\InvalidKeyException;
use Scaly\Cache\Storage\CacheStorageInterface;

class Cache implements CacheInterface
{

    /**
     * @var CacheStorageInterface
     */
    private $cacheStorage;

    private $keyRegex = '/^[a-zA-Z0-9_.]{1,64}$/';

    public function __construct($cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;
    }

    /**
     * Retrieve data from cache layer
     * @param string $key Unique identifier of your data
     * @param null $default What to return if requested data is not in cache
     * @return mixed Return cached data or default
     * @throws \Scaly\Cache\Exception\InvalidArgumentException
     */
    public function get($key, $default = null)
    {
        $this->isValidKey($key);
        try {
            if ($data = $this->cacheStorage->retrieve($key)) {
                return $data;
            }
        } catch (CacheStorageException $ex) {
            //TODO Error handling
        }
        return $default;
    }

    protected function isValidKey($key)
    {
        if (!preg_match($this->keyRegex, $key)) {
            throw new InvalidKeyException
            (
                InvalidKeyException::INVALID_KEY_EXCEPTION,
                array($key)
            );
        }
    }

    /**
     * Check if data is present on cache layer
     * @param string $key Unique identifier of your data
     * @return bool Returns if your data is present or not
     * @throws \Scaly\Cache\Exception\InvalidArgumentException
     */
    public function has($key)
    {
        $this->isValidKey($key);
        try {
            return $this->cacheStorage->check($key);
        } catch (CacheStorageException $ex) {
            //TODO Error handling
        }
        return false;
    }

    /**
     * Remove data from cache layer
     * @param string $key Unique identifier of your data
     * @return bool True if removal was successful else false
     * @throws \Scaly\Cache\Exception\InvalidArgumentException
     */
    public function delete($key)
    {
        $this->isValidKey($key);
        try {
            return $result = $this->cacheStorage->delete($key);
        } catch (CacheStorageException $ex) {
            //TODO Error handling
        }
        return false;
    }

    /**
     * Store data in cache layer
     * @param string $key Unique identifier of your data
     * @param mixed $data to store in cache layer
     * @return bool True if storing was successful else false
     * @throws \Scaly\Cache\Exception\InvalidArgumentException
     */
    public function set($key, $data)
    {
        $this->isValidKey($key);
        try {
            return $data = $this->cacheStorage->store($key, $data);
        } catch (CacheStorageException $ex) {
            //TODO Error handling
        }
        return false;
    }
}