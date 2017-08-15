<?php

namespace Scaly\Cache\Factory;

use Scaly\Cache\Storage\MemCacheStorage;
use Scaly\Core\Config\ScalyConfig;

class MemCacheStorageFactory
{

    const CONFIG_MEMCACHE_HOST = 'Memcache.Host';
    const CONFIG_MEMCACHE_PORT = 'Memcache.Port';

    public function __construct()
    {
        ScalyConfig::getInstance()->setDefaultAndSave(MemCacheStorageFactory::CONFIG_MEMCACHE_HOST, 'localhost');
        ScalyConfig::getInstance()->setDefaultAndSave(MemCacheStorageFactory::CONFIG_MEMCACHE_PORT, '11211');
    }

    public function createMemCacheStorage
    (
        $host = null,
        $port = null
    )
    {
        if (!$host) {
            $host = ScalyConfig::getInstance()->get(MemCacheStorageFactory::CONFIG_MEMCACHE_HOST);
        }
        if (!$port) {
            $port = ScalyConfig::getInstance()->get(MemCacheStorageFactory::CONFIG_MEMCACHE_PORT);
        }

        return new MemCacheStorage($host, $port);
    }

}