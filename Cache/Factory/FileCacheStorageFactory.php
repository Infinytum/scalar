<?php

namespace Scalar\Cache\Factory;


use Scalar\Cache\Storage\FileCacheStorage;
use Scalar\Core\Config\ScalarConfig;

class FileCacheStorageFactory
{

    const CONFIG_STORAGE_PATH = 'FileCache.StoragePath';

    public function __construct()
    {
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_STORAGE_PATH, sys_get_temp_dir() . '/Scalar.cache/{{App.Home}}');
    }

    /**
     * Create file cache with custom storage location
     *
     * @param string|null $storagePath
     * @return FileCacheStorage
     */
    public function createFileCacheStorage
    (
        $storagePath = null
    )
    {
        if (!is_string($storagePath)) {
            $storagePath = ScalarConfig::getInstance()->get(self::CONFIG_STORAGE_PATH);
        }
        return new FileCacheStorage($storagePath);
    }

}