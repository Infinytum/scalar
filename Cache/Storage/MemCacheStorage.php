<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 06.06.17
 * Time: 07:23
 */

namespace Scalar\Cache\Storage;


use Scalar\Cache\Exception\CacheStorageException;

class MemCacheStorage implements CacheStorageInterface
{

    /**
     * The memcache instance
     * @var \Memcached
     */
    private $memcache;

    /**
     * Determines whether the connection attempt was successful or not
     * @var bool
     */
    private $connectionSuccessful;

    /**
     * Memcache Daemon Hostname / IP
     * @var string
     */
    private $host;

    /**
     * Memcache Daemon Port
     * @var string
     */
    private $port;

    public function __construct($host, $port)
    {
        if (!class_exists("Memcached")) {
            throw new \RuntimeException
            (
                'Memcached was not found.'
            );
        }
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Check if this storage is available
     *
     * @return bool
     */
    public static function isAvailable()
    {
        return class_exists("Memcached");
    }

    /**
     * Store data in persistence layer of cache
     *
     * @param string $key Unique identifier of your data
     * @param mixed $data Data to store
     * @return bool True if storing succeeded
     * @throws CacheStorageException
     */
    public function store($key, $data)
    {
        return $this->memcache->set($key, $data);
    }

    /**
     * Retrieve data from cache
     *
     * @param string $key Unique identifier of your data
     * @return mixed Stored data from persistence
     * @throws CacheStorageException
     */
    public function retrieve($key)
    {
        return $this->memcache->get($key);
    }

    /**
     * Delete data from cache
     *
     * @param string $key Unique identifier of your data
     * @return bool True if removal was successful
     * @throws CacheStorageException
     */
    public function delete($key)
    {
        return $this->memcache->delete($key);
    }

    /**
     * Check if data exists
     *
     * @param string $key Unique identifier of your data
     * @return bool True if key was found
     * @throws CacheStorageException
     */
    public function check($key)
    {
        $this->memcache->get($key);
        return \Memcached::RES_NOTFOUND !== $this->memcache->getResultCode();
    }

    /**
     * Delete all data stored in cache
     *
     * @throws CacheStorageException
     */
    public function clear()
    {
        $this->prerequisites();

        $this->memcache->flush();
    }

    private function prerequisites()
    {

        if (!$this->memcache) {
            throw new \RuntimeException
            (
                'Connection to memcache has not been established'
            );
        }

        if (!$this->connectionSuccessful) {
            throw new \RuntimeException
            (
                'Connection to memcache could not be established'
            );
        }

    }

    /**
     * Connect to memcached server
     *
     * @return bool
     */
    public function connect()
    {
        $this->memcache = new \Memcached();
        $this->connectionSuccessful = $this->memcache->addServer
        (
            $this->host,
            $this->port
        );

        return $this->connectionSuccessful;
    }

    /**
     * Disconnect from memcached server
     *
     * @return bool
     */
    public function disconnect()
    {
        if (!$this->connectionSuccessful || !$this->memcache)
            return true;

        return $this->memcache->resetServerList();
    }
}