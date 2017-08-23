<?php
/**
 * (C) 2017 by Michael Teuscher (mk.teuscher@gmail.com)
 * as part of the Scalar PHP framework
 *
 * Released under the AGPL v3.0 license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * (C) 2017 by Michael Teuscher (mk.teuscher@gmail.com)
 * as part of the Scalar PHP framework
 *
 * Released under the AGPL v3.0 license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Scalar\Core;

require_once 'ClassLoader/AutoLoader.php';
require_once '../Util/FilterableInterface.php';
require_once '../Util/ScalarArray.php';
require_once 'Service/ServiceMap.php';
require_once '../Library/Mustache/MustacheLoader.php';

use Scalar\Cache\Cache;
use Scalar\Cache\Storage\FileCacheStorage;
use Scalar\Cache\Storage\MemCacheStorage;
use Scalar\Core\ClassLoader\AutoLoader;
use Scalar\Core\Config\ScalarConfig;
use Scalar\Core\Log\CoreLogger;
use Scalar\Core\Router\CoreRouter;
use Scalar\Core\Service\ServiceMap;
use Scalar\Core\Updater\CoreUpdater;
use Scalar\Database\DatabaseManager;
use Scalar\Http\Client\CurlHttpClient;
use Scalar\Repository\RepositoryManager;
use Scalar\Router\Router;
use Scalar\Template\Controller\AssetProxy;
use Scalar\Template\Templater;


class Scalar
{

    /**
     * Config Paths
     */
    const CONFIG_CORE_DEV_MODE = 'Core.DeveloperMode';
    const CONFIG_CORE_DEBUG_MODE = 'Core.DebugMode';
    const CONFIG_APP_PATH = 'App.Home';
    const CONFIG_APP_RESOURCE_PATH = 'App.Resources';
    const CONFIG_VIRTUAL_HOST_PREFIX = 'VirtualHost.';
    const CONFIG_ROUTER_MAP = 'Router.Map';
    const CONFIG_ROUTER_CONTROLLER = 'Router.Controller';
    const CONFIG_TEMPLATE_DIR = 'Template.Location';
    const CONFIG_ASSETS_DIR = 'Template.Assets';
    const CONFIG_UPDATE_CHANNEL = 'Updater.Channel';
    const CONFIG_STORAGE_PATH = 'FileCache.StoragePath';
    const CONFIG_MEMCACHE_HOST = 'Memcache.Host';
    const CONFIG_MEMCACHE_PORT = 'Memcache.Port';

    /**
     * Core Services
     */
    const SERVICE_AUTO_LOADER = 'AutoLoader';
    const SERVICE_CORE_LOGGER = 'Logger';
    const SERVICE_SCALAR_CONFIG = 'ScalarConfig';
    const SERVICE_ROUTER = 'Router';
    const SERVICE_MUSTACHE_ENGINE = 'MustacheEngine';
    const SERVICE_TEMPLATER = 'Templater';
    const SERVICE_DATABASE_MANAGER = 'DatabaseManager';
    const SERVICE_REPOSITORY_MANAGER = 'RepositoryManager';
    const SERVICE_UPDATER = 'Updater';
    const SERVICE_HTTP_CLIENT = 'HttpClient';

    /**
     * Services
     */
    const SERVICE_FILE_CACHE = 'FileCache';
    const SERVICE_MEM_CACHE = 'MemCache';


    /**
     * @var Scalar
     */
    private static $instance;

    /**
     * @var ServiceMap
     */
    private $serviceMap;

    private function __construct()
    {
        self::$instance = $this;
        $this->serviceMap = new ServiceMap();
    }

    /**
     * Get core instance
     * @return Scalar
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            new Scalar();
        }
        return self::$instance;
    }

    public function initialize()
    {
        $this->initializeCoreServices();
        $this->initializeAutoLoader();
        $this->initializeConfiguration();
        $this->initializeApp();
        $this->initializeServices();
    }

    private function initializeCoreServices()
    {

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_AUTO_LOADER,
            AutoLoader::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_SCALAR_CONFIG,
            ScalarConfig::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_CORE_LOGGER,
            CoreLogger::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_ROUTER,
            CoreRouter::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_MUSTACHE_ENGINE,
            \Mustache_Engine::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_TEMPLATER,
            Templater::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_DATABASE_MANAGER,
            DatabaseManager::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_REPOSITORY_MANAGER,
            RepositoryManager::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_UPDATER,
            CoreUpdater::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_HTTP_CLIENT,
            CurlHttpClient::class,
            []
        );
    }

    private function initializeAutoLoader()
    {
        /**
         * @var AutoLoader $autoLoader
         */
        $autoLoader = self::getService(self::SERVICE_AUTO_LOADER);
        $autoLoader->register();
        $autoLoader->addClassPath("Scalar\\", SCALAR_CORE);
    }

    private function initializeConfiguration()
    {
        /**
         * @var ScalarConfig $scalarConfig
         */
        $scalarConfig = self::getService
        (
            self::SERVICE_SCALAR_CONFIG
        );

        $scalarConfig->setDefaultAndSave
        (
            self::CONFIG_CORE_DEV_MODE,
            false
        )->setDefaultAndSave
        (
            self::CONFIG_CORE_DEBUG_MODE,
            false
        )->setDefaultAndSave
        (
            self::CONFIG_APP_PATH,
            dirname(SCALAR_CORE) . '/scalar_app'
        )->setDefaultAndSave
        (
            self::CONFIG_ROUTER_MAP,
            '{{App.Home}}/route.map'
        )->setDefaultAndSave
        (
            self::CONFIG_ROUTER_CONTROLLER,
            '{{App.Home}}/Controller'
        )->setDefaultAndSave
        (
            self::CONFIG_TEMPLATE_DIR,
            '{{App.Home}}/Resources/Templates/'
        )->setDefaultAndSave
        (
            self::CONFIG_ASSETS_DIR,
            '{{App.Home}}/Resources/Assets/'
        )->setDefaultAndSave
        (
            self::CONFIG_UPDATE_CHANNEL,
            'stable'
        )->setDefaultAndSave
        (
            self::CONFIG_STORAGE_PATH,
            sys_get_temp_dir() . '/Scalar.cache/{{App.Home}}'
        )->setDefaultAndSave
        (
            self::CONFIG_MEMCACHE_HOST,
            'localhost'
        )->setDefaultAndSave
        (
            self::CONFIG_MEMCACHE_PORT,
            '11211'
        );
    }

    private function initializeApp()
    {
        /**
         * @var AutoLoader $autoLoader
         */
        $autoLoader = self::getService
        (
            self::SERVICE_AUTO_LOADER
        );

        /**
         * @var ScalarConfig $scalarConfig
         */
        $scalarConfig = self::getService
        (
            self::SERVICE_SCALAR_CONFIG
        );

        $hostname = strtolower
        (
            str_replace
            (
                '.',
                '+',
                $_SERVER['HTTP_HOST']
            )
        );

        if ($scalarConfig->asScalarArray()->containsPath(self::CONFIG_VIRTUAL_HOST_PREFIX . $hostname)) {
            define
            (
                'SCALAR_APP',
                $scalarConfig
                    ->asScalarArray()
                    ->getPath
                    (
                        self::CONFIG_VIRTUAL_HOST_PREFIX . $hostname
                    )
            );

            $scalarConfig->addOverride
            (
                self::CONFIG_APP_PATH,
                SCALAR_APP
            );
        } else {
            define
            (
                'SCALAR_APP',
                $scalarConfig->get
                (
                    self::CONFIG_APP_PATH
                )
            );
        }

        $autoLoader->addClassPath
        (
            "Scalar\\App\\",
            SCALAR_APP
        );

        /**
         * @var Router $router
         */
        $router = self::getService
        (
            self::SERVICE_ROUTER
        );

        $router->addRoute('/assets', function ($request, $response, ...$params) {
            $assetProxy = new AssetProxy();
            return $assetProxy->proxy($request, $response, join('/', $params));
        });
    }

    private function initializeServices()
    {
        /**
         * @var ScalarConfig $scalarConfig
         */
        $scalarConfig = self::getService
        (
            self::SERVICE_SCALAR_CONFIG
        );

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_FILE_CACHE,
            Cache::class,
            [
                new FileCacheStorage
                (
                    $scalarConfig->asScalarArray()->get
                    (
                        self::CONFIG_STORAGE_PATH
                    )
                )
            ]
        );

        if (MemCacheStorage::isAvailable()) {
            $this->serviceMap->registerServiceClass
            (
                self::SERVICE_MEM_CACHE,
                Cache::class,
                [
                    new MemCacheStorage
                    (
                        $scalarConfig->asScalarArray()->get
                        (
                            self::CONFIG_MEMCACHE_HOST
                        ),
                        $scalarConfig->asScalarArray()->get
                        (
                            self::CONFIG_MEMCACHE_PORT
                        )
                    )
                ]
            );
        }
    }

    public static function isDeveloperMode()
    {
        $scalarConfig = self::getService
        (
            self::SERVICE_SCALAR_CONFIG
        );
        return $scalarConfig->asScalarArray()->getPath(self::CONFIG_CORE_DEV_MODE) === true;
    }

    public static function getService
    (
        $serviceName
    )
    {
        return self::getInstance()->serviceMap->getService($serviceName);
    }

    public static function getServiceMap()
    {
        return self::getInstance()->serviceMap;
    }

}