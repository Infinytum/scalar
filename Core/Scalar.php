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
use Scalar\Plugin\PluginManager;
use Scalar\Repository\RepositoryManager;
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
    const SERVICE_PLUGIN_MANAGER = 'PluginManager';

    /**
     * Services
     */
    const SERVICE_FILE_CACHE = 'FileCache';
    const SERVICE_MEM_CACHE = 'MemCache';


    /**
     * Scalar Core
     * @var Scalar
     */
    private static $instance;

    /**
     * Scalar Core Service Map
     * @var ServiceMap
     */
    private $serviceMap;

    /**
     * Scalar Core AutoLoader
     * @var AutoLoader
     */
    private $autoLoader;

    /**
     * Scalar Core Logger
     * @var CoreLogger
     */
    private $coreLogger;

    /**
     * Scalar Core Plugin Manager
     * @var PluginManager
     */
    private $pluginManager;

    /**
     * Scalar Core Configuration
     * @var ScalarConfig
     */
    private $scalarConfig;

    /**
     * Scalar Core Router
     * @var CoreRouter
     */
    private $router;

    /**
     * Never call this! Use getInstance!
     *
     * Scalar Core constructor
     */
    private function __construct()
    {
        self::$instance = $this;
        $this->serviceMap = new ServiceMap();
    }

    /**
     * Create new or fetch Scalar core instance
     * @return Scalar
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            new Scalar();
        }
        return self::$instance;
    }

    /**
     * Enable Scalar Core functionality
     */
    public function initialize()
    {
        $this->initializeCoreServices();
        $this->initializeAutoLoader();
        $this->coreLogger = self::getService(self::SERVICE_CORE_LOGGER);
        $this->initializeConfiguration();
        $this->initializeApp();
        $this->initializeServices();
        $this->initializePlugins();
    }

    /**
     * Register Core-sensitive services in the service map
     */
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

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_PLUGIN_MANAGER,
            PluginManager::class,
            [],
            true
        );
    }

    /**
     * Add Scalar Core home directory to autoloader
     */
    private function initializeAutoLoader()
    {
        $this->autoLoader = self::getService(self::SERVICE_AUTO_LOADER);
        $this->autoLoader->register();
        $this->autoLoader->addClassPath("Scalar\\", SCALAR_CORE);
    }

    /**
     * Initialize the Scalar Core configuration with it's default values
     */
    private function initializeConfiguration()
    {
        $this->coreLogger->i('Initializing Scalar configuration...');

        $this->scalarConfig = self::getService(self::SERVICE_SCALAR_CONFIG);
        $this->scalarConfig->setDefaultPath
        (
            self::CONFIG_CORE_DEV_MODE,
            false
        )->setDefaultPath
        (
            self::CONFIG_CORE_DEBUG_MODE,
            false
        )->setDefaultPath
        (
            self::CONFIG_APP_PATH,
            dirname(SCALAR_CORE) . '/scalar_app'
        )->setDefaultPath
        (
            self::CONFIG_ROUTER_MAP,
            '{{App.Home}}/route.map'
        )->setDefaultPath
        (
            self::CONFIG_ROUTER_CONTROLLER,
            '{{App.Home}}/Controller'
        )->setDefaultPath
        (
            self::CONFIG_TEMPLATE_DIR,
            '{{App.Home}}/Resources/Templates/'
        )->setDefaultPath
        (
            self::CONFIG_ASSETS_DIR,
            '{{App.Home}}/Resources/Assets/'
        )->setDefaultPath
        (
            self::CONFIG_UPDATE_CHANNEL,
            'stable'
        )->setDefaultPath
        (
            self::CONFIG_STORAGE_PATH,
            sys_get_temp_dir() . '/Scalar.cache/{{App.Home}}'
        )->setDefaultPath
        (
            self::CONFIG_MEMCACHE_HOST,
            'localhost'
        )->setDefaultPath
        (
            self::CONFIG_MEMCACHE_PORT,
            '11211'
        );

        $this->coreLogger->i('Initialized Scalar configuration successfully');
    }

    /**
     * Determine and prepare Scalar Application for prime-time
     */
    private function initializeApp()
    {
        $this->coreLogger->i("Initializing Scalar App...");

        $hostname = strtolower
        (
            str_replace
            (
                '.',
                '+',
                $_SERVER['HTTP_HOST']
            )
        );

        $this->coreLogger->d("Requested VirtualHost: " . $hostname);

        if ($this->scalarConfig->asScalarArray()->containsPath(self::CONFIG_VIRTUAL_HOST_PREFIX . $hostname)) {
            $this->coreLogger->i("Valid Virtual Hostname detected! Rerouting...");
            define
            (
                'SCALAR_APP',
                $this->scalarConfig
                    ->asScalarArray()
                    ->getPath
                    (
                        self::CONFIG_VIRTUAL_HOST_PREFIX . $hostname
                    )
            );

            $this->coreLogger->d("Rerouting to App: " . SCALAR_APP);

            $this->scalarConfig->addOverride
            (
                self::CONFIG_APP_PATH,
                SCALAR_APP
            );

            $this->coreLogger->d("Added configuration override");
        } else {
            $this->coreLogger->i("Unknown Virtual Hostname detected! Using default app");
            define
            (
                'SCALAR_APP',
                $this->scalarConfig->get
                (
                    self::CONFIG_APP_PATH
                )
            );
        }

        $this->coreLogger->d("Registered Application home in autoloader");

        $this->autoLoader->addClassPath
        (
            "Scalar\\App\\",
            SCALAR_APP
        );

        $this->router = self::getService(self::SERVICE_ROUTER);


        $this->router->addRoute('/assets', function ($request, $response, ...$params) {
            $assetProxy = new AssetProxy();
            return $assetProxy->proxy($request, $response, join('/', $params));
        });

        $this->coreLogger->d("Registered reverse asset proxy");
    }

    /**
     * Register additional, non-core-sensitive services in the service map
     */
    private function initializeServices()
    {
        $this->coreLogger->i("Initializing additional Scalar services...");

        $this->serviceMap->registerServiceClass
        (
            self::SERVICE_FILE_CACHE,
            Cache::class,
            [
                new FileCacheStorage
                (
                    $this->scalarConfig->asScalarArray()->get
                    (
                        self::CONFIG_STORAGE_PATH
                    )
                )
            ]
        );

        $this->coreLogger->d("Initialized FileCache service...");

        if (MemCacheStorage::isAvailable()) {
            $this->serviceMap->registerServiceClass
            (
                self::SERVICE_MEM_CACHE,
                Cache::class,
                [
                    new MemCacheStorage
                    (
                        $this->scalarConfig->asScalarArray()->get
                        (
                            self::CONFIG_MEMCACHE_HOST
                        ),
                        $this->scalarConfig->asScalarArray()->get
                        (
                            self::CONFIG_MEMCACHE_PORT
                        )
                    )
                ]
            );

            $this->coreLogger->d("Initialized MemCache service...");
        }
    }

    /**
     * Load global and app specific plugins
     */
    private function initializePlugins()
    {
        $this->coreLogger->i("Initializing Scalar Plugin framework...");
        $this->pluginManager = self::getService(self::SERVICE_PLUGIN_MANAGER);
        $this->coreLogger->i("Loading all plugins");
        $this->pluginManager->loadPluginDirectory();
        $this->coreLogger->i("Loaded all plugins");
    }

    /**
     * Returns true if developer mode was set to 'on' in the core configuration
     * @return bool
     */
    public static function isDeveloperMode()
    {
        return self::getInstance()->scalarConfig->asScalarArray()->getPath(self::CONFIG_CORE_DEV_MODE) === true;
    }

    /**
     * Returns a service instance or a value if found. Else will return null
     *
     * @param string $serviceName
     * @return mixed|null|object
     */
    public static function getService
    (
        $serviceName
    )
    {
        return self::getInstance()->serviceMap->getService($serviceName);
    }

    /**
     * Get Core Service Map
     *
     * @return ServiceMap
     */
    public static function getServiceMap()
    {
        return self::getInstance()->serviceMap;
    }

    /**
     * Disables Scalar Core functionality
     */
    public function shutdown()
    {
        $this->coreLogger->i("Shutting down Scalar...");

        $this->pluginManager->disableAllPlugins();
    }

}