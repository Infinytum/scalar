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

require_once 'AppInterface.php';
require_once 'Service/CoreService.php';
require_once 'Service/CoreLoaderService.php';
require_once '../Util/FilterableInterface.php';
require_once '../Util/ScalarArray.php';
require_once '../Service/ServiceMap.php';

use Scalar\App\App;
use Scalar\Cache\Cache;
use Scalar\Cache\Storage\FileCacheStorage;
use Scalar\Cache\Storage\MemCacheStorage;
use Scalar\Core\Service\CoreConfigurationService;
use Scalar\Core\Service\CoreDatabaseService;
use Scalar\Core\Service\CoreLoaderService;
use Scalar\Core\Service\CoreLoggerService;
use Scalar\Core\Service\CorePluginService;
use Scalar\Core\Service\CoreRouterService;
use Scalar\Core\Service\CoreTemplateService;
use Scalar\Core\Updater\CoreUpdater;
use Scalar\Http\Client\CurlHttpClient;
use Scalar\Http\Message\RequestInterface;
use Scalar\Http\Message\ResponseInterface;
use Scalar\Repository\RepositoryManager;
use Scalar\Router\AppInterface;
use Scalar\Service\ServiceMap;


class Scalar implements AppInterface
{

    /**
     * Config Paths
     */
    const CONFIG_CORE_DEV_MODE = 'Core.DeveloperMode';
    const CONFIG_CORE_DEBUG_MODE = 'Core.DebugMode';
    const CONFIG_APP_PATH = 'App.Home';
    const CONFIG_APP_RESOURCE_PATH = 'App.Resources';
    const CONFIG_VIRTUAL_HOST_PREFIX = 'VirtualHost.';
    const CONFIG_UPDATE_CHANNEL = 'Updater.Channel';
    const CONFIG_STORAGE_PATH = 'FileCache.StoragePath';
    const CONFIG_MEMCACHE_HOST = 'Memcache.Host';
    const CONFIG_MEMCACHE_PORT = 'Memcache.Port';

    /**
     * Core Services
     */
    const SERVICE_CORE_LOADER = 'CoreLoader';
    const SERVICE_CORE_LOGGER = 'CoreLogger';
    const SERVICE_CORE_CONFIG = 'CoreConfig';
    const SERVICE_CORE_ROUTER = 'CoreRouter';
    const SERVICE_CORE_PLUGIN = 'CorePlugin';
    const SERVICE_CORE_TEMPLATE = 'CoreTemplate';
    const SERVICE_CORE_DATABASE = 'CoreDatabase';
    const SERVICE_REPOSITORY_MANAGER = 'RepositoryManager';
    const SERVICE_UPDATER = 'Updater';
    const SERVICE_HTTP_CLIENT = 'HttpClient';

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
     * @var CoreLoaderService
     */
    private $coreLoader;

    /**
     * Scalar Core Logger
     * @var CoreLoggerService
     */
    private $coreLogger;

    /**
     * Scalar Core Plugin Manager
     * @var CorePluginService
     */
    private $corePlugin;

    /**
     * Scalar Core Configuration
     * @var CoreConfigurationService
     */
    private $coreConfig;

    /**
     * Scalar Core Router
     * @var CoreRouterService
     */
    private $coreRouter;

    /**
     * Scalar Core sub application
     * @var AppInterface
     */
    private $app;

    /**
     * CoreTemplate instance
     * @var CoreTemplateService
     */
    private $coreTemplate;

    /**
     * CoreDatabase instance
     * @var CoreDatabaseService
     */
    private $coreDatabase;

    /**
     * Never call this! Use getInstance!
     *
     * Scalar Core constructor
     */
    private function __construct()
    {
        self::$instance = $this;
        $this->serviceMap = new ServiceMap();
        $this->initializeAutoLoader();
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
     * Add Scalar Core home directory to autoloader
     */
    private function initializeAutoLoader()
    {
        $this->serviceMap->registerServiceClass // Upgrade done
        (
            self::SERVICE_CORE_LOADER,
            CoreLoaderService::class,
            [],
            true
        );

        $this->coreLoader = self::getService(self::SERVICE_CORE_LOADER);
    }

    /**
     * Register Core-sensitive services in the service map
     */
    private function initializeCoreServices()
    {

        $this->serviceMap->registerServiceClass // Upgrade done
        (
            self::SERVICE_CORE_CONFIG,
            CoreConfigurationService::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass // Upgrade done
        (
            self::SERVICE_CORE_LOGGER,
            CoreLoggerService::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass // Upgrade done
        (
            self::SERVICE_CORE_ROUTER,
            CoreRouterService::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass // Upgrade done
        (
            self::SERVICE_CORE_TEMPLATE,
            CoreTemplateService::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass // Upgrade
        (
            self::SERVICE_CORE_DATABASE,
            CoreDatabaseService::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass // Pending
        (
            self::SERVICE_REPOSITORY_MANAGER,
            RepositoryManager::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass // Pending
        (
            self::SERVICE_UPDATER,
            CoreUpdater::class,
            [],
            true
        );

        $this->serviceMap->registerServiceClass // No Upgrade needed
        (
            self::SERVICE_HTTP_CLIENT,
            CurlHttpClient::class,
            []
        );

        $this->serviceMap->registerServiceClass // Upgrade
        (
            self::SERVICE_CORE_PLUGIN,
            CorePluginService::class,
            [],
            true
        );
    }

    /**
     * Initialize the Scalar Core configuration with it's default values
     */
    private function initializeConfiguration()
    {
        $this->coreLogger->i('Initializing Scalar configuration...');

        $this->coreConfig = self::getService(self::SERVICE_CORE_CONFIG);

        $this->coreConfig->setDefault
        (
            self::CONFIG_CORE_DEV_MODE,
            false
        )->setDefault
        (
            self::CONFIG_CORE_DEBUG_MODE,
            false
        )->setDefault
        (
            self::CONFIG_APP_PATH,
            dirname(SCALAR_CORE) . '/scalar_app'
        )->setDefault
        (
            self::CONFIG_UPDATE_CHANNEL,
            'stable'
        )->setDefault
        (
            self::CONFIG_STORAGE_PATH,
            sys_get_temp_dir() . '/Scalar.cache/{{App.Home}}'
        )->setDefault
        (
            self::CONFIG_MEMCACHE_HOST,
            'localhost'
        )->setDefault
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

        if ($this->coreConfig->has(self::CONFIG_VIRTUAL_HOST_PREFIX . $hostname)) {
            $this->coreLogger->i("Valid Virtual Hostname detected! Rerouting...");
            define
            (
                'SCALAR_APP',
                $this->coreConfig
                    ->get
                    (
                        self::CONFIG_VIRTUAL_HOST_PREFIX . $hostname
                    )
            );

            $this->coreLogger->d("Rerouting to App: " . SCALAR_APP);

            $this->coreConfig->addOverride
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
                $this->coreConfig->get
                (
                    self::CONFIG_APP_PATH
                )
            );
        }

        $this->coreLogger->d("Registered Application home in autoloader");

        $this->coreLoader->registerNamespace
        (
            "Scalar\\App\\",
            SCALAR_APP
        );

        $this->coreRouter = Scalar::getService(Scalar::SERVICE_CORE_ROUTER);
        $this->coreTemplate = Scalar::getService(Scalar::SERVICE_CORE_TEMPLATE);
        $this->coreDatabase = Scalar::getService(Scalar::SERVICE_CORE_DATABASE);
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
                    $this->coreConfig->get
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
                        $this->coreConfig->get
                        (
                            self::CONFIG_MEMCACHE_HOST
                        ),
                        $this->coreConfig->get
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
     * Returns true if developer mode was set to 'on' in the core configuration
     * @return bool
     */
    public static function isDeveloperMode()
    {
        return self::getInstance()->coreConfig->get(self::CONFIG_CORE_DEV_MODE) === true;
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
     * This function is being executed before the request is dispatched
     *
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function startup
    (
        $request
    )
    {
        $this->initializeCoreServices();
        $this->coreLogger = self::getService(self::SERVICE_CORE_LOGGER);
        $this->initializeConfiguration();
        $this->initializeApp();
        $this->coreLogger->setup(false);
        $this->initializeServices();

        $this->corePlugin = Scalar::getService(Scalar::SERVICE_CORE_PLUGIN);

        $this->app = new App;
        $this->app->startup($request);

        return $request;
    }

    /**
     * This function is being executed after the request has been dispatched
     * and the response is ready to be returned to the client
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function shutdown(
        $request,
        $response
    )
    {
        $this->coreLogger->i("Shutting down Scalar...");
        $this->app->shutdown($request, $response);
        $this->coreRouter->tearDown();
        $this->corePlugin->tearDown();
        $this->coreTemplate->tearDown();
        $this->coreDatabase->tearDown();
        $this->coreLogger->tearDown(true);
        $this->coreConfig->tearDown();
        $this->coreLogger->tearDown();

        return $response;
    }
}