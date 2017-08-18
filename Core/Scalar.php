<?php

namespace Scalar\Core;

require_once 'ClassLoader/AutoLoader.php';

use Scalar\Core\ClassLoader\AutoLoader;
use Scalar\Core\Config\ScalarConfig;
use Scalar\Core\Log\CoreLogger;
use Scalar\Database\DatabaseManager;
use Scalar\IO\Factory\StreamFactory;
use Scalar\Library\Mustache\MustacheLoader;
use Scalar\Router\Hook\MethodFilterMiddleware;
use Scalar\Router\Hook\RestControllerHook;
use Scalar\Router\Router;
use Scalar\Template\Controller\AssetProxy;
use Scalar\Template\Hook\TemplateHook;


class Scalar
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
     * @var Scalar
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
        $autoLoader->addClassPath("Scalar\\", SCALAR_CORE);
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_DEV_MODE, false);
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_DEBUG_MODE, false);
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
        $hostname = strtolower(str_replace('.', '+', $_SERVER['HTTP_HOST']));
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_APP_PATH, dirname(SCALAR_CORE) . '/SCALAR_APP');
        if (ScalarConfig::getInstance()->asScalarArray()->containsPath('VirtualHost.' . $hostname)) {
            define('SCALAR_APP', ScalarConfig::getInstance()->asScalarArray()->getPath('VirtualHost.' . $hostname));
            ScalarConfig::getInstance()->addOverride(self::CONFIG_APP_PATH, SCALAR_APP);
        } else {
            define('SCALAR_APP', ScalarConfig::getInstance()->get(self::CONFIG_APP_PATH));
        }
        $autoLoader = AutoLoader::getInstance();
        $autoLoader->addClassPath("Scalar\\App\\", SCALAR_APP);
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
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_LOG_ENABLED, false);
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_LOG_APPEND, true);
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_LOG_FILE, '{{App.Home}}/scalar.log');
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_CORE_LOG_LEVEL, CoreLogger::Warning);

        $streamFactory = new StreamFactory();
        $logStream = null;

        if (ScalarConfig::getInstance()->get(self::CONFIG_CORE_LOG_ENABLED) === true) {
            $mode = ScalarConfig::getInstance()->get(self::CONFIG_CORE_LOG_APPEND) ? 'a+' : 'w+';
            $logStream = $streamFactory->createStreamFromFile
            (
                ScalarConfig::getInstance()->get(self::CONFIG_CORE_LOG_FILE),
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
            ScalarConfig::getInstance()->get(self::CONFIG_CORE_LOG_LEVEL)
        );
        $this->coreLogger->v('Core Logger initialized');
    }

    /**
     * Initialize Scalar router and router middleware
     */
    private function initializeRouter()
    {
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_ROUTER_MAP, '{{App.Home}}/route.map');

        $this->coreLogger->v('Initializing router...');

        $this->router = new Router
        (
            ScalarConfig::getInstance()->get(self::CONFIG_ROUTER_MAP),
            SCALAR_APP . '/Controller'
        );

        $this->coreLogger->v('Injecting middleware...');
        $this->router->addHandler(new MethodFilterMiddleware());
        $this->router->addHandler(new TemplateHook());
        $this->router->addHandler(new RestControllerHook());

        if (
            ScalarConfig::getInstance()->get(self::CONFIG_CORE_DEV_MODE) ||
            !file_exists(ScalarConfig::getInstance()->get(self::CONFIG_ROUTER_MAP))
        ) {
            $this->coreLogger->v('Generating route map');
            $this->router->generateRouteMap();
        }

        $this->coreLogger->v('Router initialization complete');
    }

    private function initializeTemplater()
    {
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_TEMPLATE_DIR, '{{App.Home}}/Resources/Templates/');
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_ASSETS_DIR, '{{App.Home}}/Resources/Assets/');
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