<?php

namespace Scaly\Core;

require_once 'ClassLoader/AutoLoader.php';

use Scaly\Core\ClassLoader\AutoLoader;
use Scaly\Core\Config\ScalyConfig;
use Scaly\Core\Log\CoreLogger;
use Scaly\Database\DatabaseManager;
use Scaly\IO\Factory\StreamFactory;
use Scaly\Library\Mustache\MustacheLoader;
use Scaly\Router\Hook\MethodFilterMiddleware;
use Scaly\Router\Hook\RestControllerHook;
use Scaly\Router\Router;
use Scaly\Template\Controller\AssetProxy;
use Scaly\Template\Hook\TemplateHook;


class Scaly
{

    const CONFIG_CORE_DEV_MODE = 'Core.DeveloperMode';
    const CONFIG_CORE_DEBUG_MODE = 'Core.DebugMode';
    const CONFIG_CORE_LOG_ENABLED = 'Core.Logging';
    const CONFIG_CORE_LOG_FILE = 'Core.LogPath';
    const CONFIG_CORE_LOG_LEVEL = 'Core.LogLevel';
    const CONFIG_CORE_LOG_APPEND = 'Core.LogAppend';
    const CONFIG_APP_PATH = 'App.Home';
    const CONFIG_APP_RESOURCE_PATH = 'App.Resources';
    const CONFIG_ROUTER_MAP = 'Router.Map';
    const CONFIG_TEMPLATE_DIR = 'Template.Location';
    const CONFIG_ASSETS_DIR = 'Template.Assets';

    /**
     * @var Scaly
     */
    private static $instance;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var CoreLogger
     */
    private $coreLogger;

    private function __construct()
    {
        self::$instance = $this;
        $autoLoader = AutoLoader::getInstance();
        $autoLoader->register();
        $autoLoader->addClassPath("Scaly\\", SCALY_CORE);
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_DEV_MODE, false);
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_DEBUG_MODE, false);
    }

    /**
     * Get core logger
     * @return CoreLogger
     */
    public static function getLogger()
    {
        return self::getInstance()->coreLogger;
    }

    /**
     * Get core instance
     * @return Scaly
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            new Scaly();
        }
        return self::$instance;
    }

    public function initialize()
    {
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_APP_PATH, dirname(SCALY_CORE) . '/scaly_app');
        if (ScalyConfig::getInstance()->asScalyArray()->containsPath('VirtualHost.' . strtolower($_SERVER['SERVER_NAME']))) {
            define('SCALY_APP', ScalyConfig::getInstance()->asScalyArray()->getPath('VirtualHost.' . strtolower($_SERVER['SERVER_NAME'])));
            ScalyConfig::getInstance()->addOverride(self::CONFIG_APP_PATH, SCALY_APP);
        } else {
            define('SCALY_APP', ScalyConfig::getInstance()->get(self::CONFIG_APP_PATH));
        }
        $autoLoader = AutoLoader::getInstance();
        $autoLoader->addClassPath("Scaly\\App\\", SCALY_APP);
        $this->initializeCoreLogger();
        $this->initializeRouter();
        $this->initializeTemplater();
        $this->initializeDatabaseEngine();

        $this->coreLogger->v('Core initialization complete');
    }

    /**
     * Initialize core logger
     */
    private function initializeCoreLogger()
    {
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_LOG_ENABLED, false);
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_LOG_APPEND, true);
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_LOG_FILE, '{{App.Home}}/scaly.log');
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_LOG_LEVEL, CoreLogger::Warning);

        $streamFactory = new StreamFactory();
        $logStream = null;

        if (ScalyConfig::getInstance()->get(self::CONFIG_CORE_LOG_ENABLED)) {
            $mode = ScalyConfig::getInstance()->get(self::CONFIG_CORE_LOG_APPEND) ? 'a+' : 'w+';
            $logStream = $streamFactory->createStreamFromFile
            (
                ScalyConfig::getInstance()->get(self::CONFIG_CORE_LOG_FILE),
                $mode
            );

            if (!$logStream) {
                $logStream = $streamFactory->createStream();
            }

        } else {
            $logStream = $streamFactory->createStream();
        }


        $this->coreLogger = new CoreLogger
        (
            $logStream,
            ScalyConfig::getInstance()->get(self::CONFIG_CORE_LOG_LEVEL)
        );
        $this->coreLogger->v('Core Logger initialized');
    }

    /**
     * Initialize Scaly router and router middleware
     */
    private function initializeRouter()
    {
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_ROUTER_MAP, '{{App.Home}}/route.map');

        $this->coreLogger->v('Initializing router...');

        $this->router = new Router
        (
            ScalyConfig::getInstance()->get(self::CONFIG_ROUTER_MAP),
            SCALY_APP . '/Controller'
        );

        $this->coreLogger->v('Injecting middleware...');
        $this->router->addHandler(new MethodFilterMiddleware());
        $this->router->addHandler(new TemplateHook());
        $this->router->addHandler(new RestControllerHook());

        if (
            ScalyConfig::getInstance()->get(self::CONFIG_CORE_DEV_MODE) ||
            !file_exists(ScalyConfig::getInstance()->get(self::CONFIG_ROUTER_MAP))
        ) {
            $this->coreLogger->v('Generating route map');
            $this->router->generateRouteMap();
        }

        $this->coreLogger->v('Router initialization complete');
    }

    private function initializeTemplater()
    {
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_TEMPLATE_DIR, '{{App.Home}}/Resources/Templates/');
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_ASSETS_DIR, '{{App.Home}}/Resources/Assets/');
        new MustacheLoader();
        self::getRouter()->addRoute('/assets', function ($request, $response, ...$params) {
            $assetProxy = new AssetProxy();
            return $assetProxy->proxy($request, $response, join('/', $params));
        });
    }

    /**
     * Get core router
     * @return Router
     */
    public static function getRouter()
    {
        return self::getInstance()->router;
    }

    private function initializeDatabaseEngine()
    {
        $this->coreLogger->v('Initializing database engine...');
        $databaseManager = DatabaseManager::getInstance();
    }

}