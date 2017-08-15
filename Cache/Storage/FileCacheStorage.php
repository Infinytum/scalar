<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 08.05.17
 * Time: 21:45
 */

namespace Scaly\Cache\Storage;


use Scaly\Cache\Exception\CacheStorageException;
use Scaly\Cache\Exception\IllegalDirectoryTraversalException;
use Scaly\Cache\Exception\PreconditionsNotMetException;
use Scaly\IO\Stream\Stream;

class FileCacheStorage implements CacheStorageInterface
{

    private $cacheLocation = null;

    /**
     * FileCacheStorage constructor.
     * @param string $storagePath
     */
    public function __construct($storagePath)
    {
        $this->cacheLocation = $storagePath;
    }

    /**
     * Check if this storage is available
     *
     * @return bool
     */
    public static function isAvailable()
    {
        return true;
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
        $this->preconditions();
        $cacheObject = $this->generateCachePath($key);
        $serializedString = @serialize($data);

        $rawCacheStream = @fopen($cacheObject, "w+");
        if (!$rawCacheStream) {
            return false;
        }

        $cacheStream = new Stream($rawCacheStream);
        $cacheStream->write($serializedString);
        $cacheStream->close();

        return $this->check($key);
    }

    /**
     * Check if all preconditions are met for the file cache
     * to work.
     *
     * @throws CacheStorageException
     */
    private function preconditions()
    {

        if (!file_exists($this->getCacheLocation())) {
            if (!@mkdir($this->getCacheLocation(), 0777, true)) {
                throw new PreconditionsNotMetException
                (
                    PreconditionsNotMetException::PRECONDITION_WRITE_PERMISSION,
                    array($this->getCacheLocation())
                );
            }
        }

        if (!is_readable($this->getCacheLocation())) {
            throw new PreconditionsNotMetException
            (
                PreconditionsNotMetException::PRECONDITION_READ_PERMISSION,
                array($this->getCacheLocation())
            );
        }

        if (!is_writable($this->getCacheLocation())) {
            throw new PreconditionsNotMetException
            (
                PreconditionsNotMetException::PRECONDITION_WRITE_PERMISSION,
                array($this->getCacheLocation())
            );
        }
    }

    /**
     * @return string Cache Location
     */
    public function getCacheLocation()
    {
        return $this->cacheLocation;
    }

    /**
     * @param string $cacheLocation
     * @return FileCacheStorage
     */
    public function setCacheLocation($cacheLocation)
    {
        $this->cacheLocation = $cacheLocation;
        return $this;
    }

    /**
     * Generates a path for specified key relative to cache storage
     *
     * @param string $key Unique Key
     * @return string
     * @throws CacheStorageException
     */
    private function generateCachePath($key)
    {
        $userPath = $this->getCacheLocation() . $key;

        if (strpos($userPath, $this->getCacheLocation()) !== 0) {
            throw new IllegalDirectoryTraversalException
            (
                IllegalDirectoryTraversalException::ILLEGAL_DIRECTORY_TRAVERSAL,
                array($key)
            );
        } else {
            return $this->getCacheLocation() . $key;
        }
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
        $this->preconditions();
        $cacheObject = $this->generateCachePath($key);
        return file_exists($cacheObject);
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
        $this->preconditions();
        if (!$this->check($key)) {
            return true;
        }
        $cacheObject = $this->generateCachePath($key);
        return @unlink($cacheObject);
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
        $this->preconditions();
        if (!$this->check($key)) {
            return null;
        }
        $cacheObject = $this->generateCachePath($key);

        $rawCacheStream = @fopen($cacheObject, "r");
        if (!$rawCacheStream) {
            return null;
        }

        $cacheStream = new Stream($rawCacheStream);
        $serializedString = $cacheStream->getContents();
        $cacheStream->close();

        return @unserialize($serializedString);
    }

    /**
     * Delete all data stored in cache
     *
     * @throws CacheStorageException
     */
    public function clear()
    {
        $this->preconditions();
        $di = new \RecursiveDirectoryIterator($this->getCacheLocation(), \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($ri as $file) {
            $file->isDir() ? rmdir($file) : unlink($file);
        }
    }

    /**
     * Get timestamp of creation date in persistence layer
     *
     * @param string $key Unique identifier of your data
     * @return mixed UNIX Timestamp
     * @throws CacheStorageException
     */
    public function getCreationDate($key)
    {
        $this->preconditions();
        if (!$this->check($key)) {
            return false;
        }
        $cacheObject = $this->generateCachePath($key);

        return filemtime($cacheObject);
    }
}