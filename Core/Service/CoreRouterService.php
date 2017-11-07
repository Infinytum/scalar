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

namespace Scalar\Core\Service;


use Scalar\Config\JsonConfig;
use Scalar\Core\Router\Hook\ControllerDependencyInjectionHook;
use Scalar\Core\Router\Hook\MethodFilterMiddleware;
use Scalar\Core\Router\Hook\MinifierMiddleware;
use Scalar\Core\Router\Hook\RestControllerHook;
use Scalar\Core\Router\RouteMapGenerator;
use Scalar\Core\Scalar;
use Scalar\Http\Message\Response;
use Scalar\Http\Message\ServerRequestInterface;
use Scalar\Http\Middleware\HttpMiddlewareDispatcher;
use Scalar\Http\Middleware\HttpMiddlewareInterface;
use Scalar\IO\File;
use Scalar\IO\Stream\Stream;
use Scalar\Router\RouteEntry;
use Scalar\Router\Router;
use Scalar\Router\RoutingTable;

class CoreRouterService extends CoreService
{

    // Configuration

    const CONFIG_ROUTER_MAP = 'Map';
    const CONFIG_ROUTER_CONTROLLER = 'Controller';

    /**
     * CoreLogger instance
     * @var CoreLoggerService
     */
    private $coreLogger;

    /**
     * Router instance
     * @var Router
     */
    private $router;

    /**
     * RoutingTable file storage
     * @var JsonConfig
     */
    private $routingTableFile;

    /**
     * Middleware Dispatcher
     * @var HttpMiddlewareDispatcher
     */
    private $httpMiddlewareDispatcher;

    /**
     * Array
     * @var array
     */
    private $initialArray;

    /**
     * CoreRouterService constructor.
     */
    public function __construct()
    {
        $this->coreLogger = Scalar::getService(Scalar::SERVICE_CORE_LOGGER);
        parent::__construct('Router');
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @return Response
     */
    public function dispatch
    (
        $serverRequest
    )
    {
        $stream = new Stream(fopen("php://temp", "r+"));


        $route = $this->generateCoreHandler($serverRequest->getUri()->getPath());

        $response = new Response
        (
            $stream,
            [],
            $route->getData()->asArray()
        );

        return $this->httpMiddlewareDispatcher->dispatch($serverRequest, $response, $route->getHandler());
    }

    private function generateCoreHandler
    (
        $route
    )
    {
        $path = strtolower($route);
        $array = explode("/", $path);
        $routeEntry = null;
        foreach ($array as $key) {
            $checkPath = join("/", $array);
            if ($routeEntry = $this->router->getRoutingTable()->resolveRoute($checkPath))
                break;
            array_pop($array);
        }

        if ($routeEntry === null) {
            return new RouteEntry('/', function ($request, $response) {
                return $response;
            });
        }

        $uri = str_ireplace($routeEntry->getRoute(), '', $route);
        $path = explode('/', $uri);
        unset($path[0]);

        $retVal = clone $routeEntry;

        $retVal->setHandler(function ($request, $response) use ($path, $routeEntry) {
            array_unshift($path, $response);
            array_unshift($path, $request);
            if ($response->hasCustomArgument('Controller')) {
                $controllerName = $response->getCustomArgument('Controller');
                $functionName = $response->getCustomArgument('Function');
                $controller = new $controllerName($request);
                return call_user_func_array(array($controller, $functionName), $path);
            } else {
                return call_user_func_array($routeEntry->getHandler(), $path);
            }
        });

        return $retVal;
    }

    /**
     * Inject middleware into the router
     *
     * @param HttpMiddlewareInterface $middleware
     */
    public function addMiddleware
    (
        $middleware
    )
    {
        $this->httpMiddlewareDispatcher = $this->httpMiddlewareDispatcher->addMiddleware($middleware);
    }

    /**
     * Get current middleware dispatcher
     * @return HttpMiddlewareDispatcher
     */
    public function getMiddlewareDispatcher()
    {
        return $this->httpMiddlewareDispatcher;
    }

    /**
     * Get current routing table
     * @return RoutingTable
     */
    public function getRoutingTable()
    {
        return $this->router->getRoutingTable();
    }

    /**
     * Initialize service for work
     *
     * @return bool
     */
    public function setup()
    {
        $this->coreLogger->i("Initializing CoreRouter...");

        $this->addDefault(self::CONFIG_ROUTER_CONTROLLER, '{{App.Home}}/Controller');
        $this->addDefault(self::CONFIG_ROUTER_MAP, '{{App.Home}}/route.map');

        $file = new File($this->getValue(self::CONFIG_ROUTER_MAP), true);

        if (Scalar::isDeveloperMode() || !$file->exists()) {
            $generatedRouteMap = RouteMapGenerator::fromApp($this->getValue(self::CONFIG_ROUTER_CONTROLLER));
        }

        if ((!$file->exists() && !$file->canCreate()) || (!$file->isWritable() && $file->exists())) {
            $this->coreLogger->e('Cannot create routing table! Fail-over to in-memory routing');
            $file = fopen('php://temp', 'r+');
            $generatedRouteMap = RouteMapGenerator::fromApp($this->getValue(self::CONFIG_ROUTER_CONTROLLER));
        }

        $this->routingTableFile = new JsonConfig($file);
        $this->routingTableFile->load();

        $this->initialArray = $this->routingTableFile->asScalarArray()->asArray();

        if (!$this->routingTableFile->asScalarArray()->any()) {
            $generatedRouteMap = RouteMapGenerator::fromApp($this->getValue(self::CONFIG_ROUTER_CONTROLLER));
        }

        if (isset($generatedRouteMap)) {
            $this->routingTableFile->setConfigArray($generatedRouteMap);
        }


        $routingTable = new RoutingTable($this->routingTableFile->asScalarArray()->asArray());

        $this->router = new Router($routingTable);

        $this->httpMiddlewareDispatcher = new HttpMiddlewareDispatcher();

        $this->httpMiddlewareDispatcher = $this->httpMiddlewareDispatcher
            ->addMiddleware(new MethodFilterMiddleware())
            ->addMiddleware(new RestControllerHook())
            ->addMiddleware(new ControllerDependencyInjectionHook());

        return true;
    }

    /**
     * Destruct service and execute tearDown tasks
     *
     * @return bool
     */
    public function tearDown()
    {
        $this->coreLogger->i("Tearing down CoreRouter...");

        if ($this->initialArray != $this->router->getRoutingTable()->getRoutingTable()->asArray()) {
            $this->coreLogger->i("Saving routing table...");
            $this->routingTableFile->setConfigArray($this->router->getRoutingTable()->getRoutingTable()->asArray());


            try {
                $this->routingTableFile->save();
            } catch (\Exception $exception) {
                $this->coreLogger->e('An error occurred while saving the routing tables: ' . $exception);
            }
        }
        return true;
    }
}