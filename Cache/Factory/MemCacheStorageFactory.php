<?php

namespace Scalar\Cache\Factory;

use Scalar\Cache\Storage\MemCacheStorage;
use Scalar\Core\Config\ScalarConfig;

class MemCacheStorageFactory
{

    const CONFIG_MEMCACHE_HOST = 'Memcache.Host';
    const CONFIG_MEMCACHE_PORT = 'Memcache.Port';

    public function __construct()
    {
        ScalarConfig::getInstance()->setDefaultAndSave(MemCacheStorageFactory::CONFIG_MEMCACHE_HOST, 'localhost');
        ScalarConfig::getInstance()->setDefaultAndSave(MemCacheStorageFactory::CONFIG_MEMCACHE_PORT, '11211');
    }

    public function createMemCacheStorage
    (
        $host = null,
        $port = null
    )
    {
        if (!$host) {
            $host = ScalarConfig::getInstance()->get(MemCacheStorageFactory::CONFIG_MEMCACHE_HOST);
        }
        if (!$port) {
            $port = ScalarConfig::getInstance()->get(MemCacheStorageFactory::CONFIG_MEMCACHE_PORT);
        }

        return new MemCacheStorage($host, $port);
    }

}