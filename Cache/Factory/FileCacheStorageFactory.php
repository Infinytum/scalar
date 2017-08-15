<?php

namespace Scaly\Cache\Factory;


use Scaly\Cache\Storage\FileCacheStorage;
use Scaly\Core\Config\ScalyConfig;

class FileCacheStorageFactory
{

    const CONFIG_STORAGE_PATH = 'FileCache.StoragePath';

    public function __construct()
    {
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_STORAGE_PATH, sys_get_temp_dir() . '/scaly.cache/{{App.Home}}');
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
            $storagePath = ScalyConfig::getInstance()->get(self::CONFIG_STORAGE_PATH);
        }
        return new FileCacheStorage($storagePath);
    }

}