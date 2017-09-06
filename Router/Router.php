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
 * Created by PhpStorm.
 * User: nila
 * Date: 05.06.17
 * Time: 17:58
 */

namespace Scalar\Router;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Scalar\App\App;
use Scalar\Config\Config;
use Scalar\Core\ClassLoader\AutoLoader;
use Scalar\Core\Scalar;
use Scalar\Http\Message\Response;
use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;
use Scalar\Http\Middleware\HttpMiddlewareDispatcher;
use Scalar\Http\Middleware\HttpMiddlewareInterface;
use Scalar\IO\Stream\Stream;
use Scalar\IO\UriInterface;
use Scalar\Util\ScalarArray;

class Router implements RouterInterface
{

    /**
     * @var HttpMiddlewareDispatcher
     */
    private $middlewareDispatcher;

    /**
     * @var string
     */
    private $phpDocRegex = '/@(?<property>[A-Z][a-z]+)(?:\s){0,1}(?<values>.*)/';

    /**
     * @var Config
     */
    private $routeMap;

    /**
     * @var array
     */
    private $tempRouteMap;

    /**
     * @var string
     */
    private $controllerLocation;

    /**
     * Router constructor.
     * @param Config $routeMap
     * @param string $controllerLocation
     * @param array $tempRouteMap
     */
    function __construct
    (
        $routeMap,
        $controllerLocation,
        $tempRouteMap = []
    )
    {
        /**
         * @var AutoLoader $autoLoader
         */
        $autoLoader = Scalar::getService
        (
            Scalar::SERVICE_AUTO_LOADER
        );

        $this->routeMap = $routeMap;

        $this->controllerLocation = $controllerLocation;
        $this->middlewareDispatcher = new HttpMiddlewareDispatcher([]);
        $autoLoader->addClassPath("\\", $controllerLocation);

        $this->tempRouteMap = $tempRouteMap;


        if (!$routeMap->has('routes') || Scalar::isDeveloperMode()) {
            $this->regenerateRouteMap();
        }

        $this->routeMap->load();
    }

    public function regenerateRouteMap()
    {
        $classes = [];
        $routes = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->controllerLocation . '/')) as $item) {
            if (strpos($item, '.php') < 1) {
                continue;
            }
            require_once $item;
            $ns = $this->extract_namespace($item);
            array_push($classes, $ns . "\\" . pathinfo(basename($item), PATHINFO_FILENAME));
        }

        foreach ($classes as $item) {
            $controller = new \stdClass();
            $reflector = new \ReflectionClass($item);
            $controllerData = $reflector->getDocComment();
            preg_match_all($this->phpDocRegex, $controllerData, $matches, PREG_SET_ORDER, 0);
            foreach ($matches as $match) {
                $property = $match["property"];
                $values = str_getcsv($match["values"], ' ');
                if (is_array($values) && count($values) == 1)
                    $values = $values[0];
                $controller->$property = $values;
            }

            if (!isset($controller->Path))
                $controller->Path = '/' . strtolower(str_replace('Controller', '', $reflector->getShortName()));

            if (is_array($controller->Path)) {
                foreach ($controller->Path as $item2) {
                    $controllerClone = clone $controller;
                    $controllerClone->Path = $item2;
                    $routes = $routes + $this->generateMethodMap($reflector, $controllerClone, $item);
                }
            } else {
                $routes = $routes + $this->generateMethodMap($reflector, $controller, $item);
            }


        }

        $this->routeMap->set("routes", $routes);
        $this->routeMap->save();
        $this->routeMap->load();

    }

    private function extract_namespace($file)
    {
        $ns = NULL;
        $handle = fopen($file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (strpos($line, 'namespace') === 0) {
                    $parts = explode(' ', $line);
                    $ns = rtrim(trim($parts[1]), ';');
                    break;
                }
            }
            fclose($handle);
        }
        return $ns;
    }

    /**
     * @param \ReflectionClass $controllerReflect
     * @param $controller
     * @param $controllerName
     * @return array
     */
    private function generateMethodMap($controllerReflect, $controller, $controllerName)
    {
        $routes = [];
        foreach ($controllerReflect->getMethods() as $phpMethod) {
            $method = new \stdClass();
            $method->Controller = $controllerName;
            $method->Function = $phpMethod->getName();
            $methodData = $phpMethod->getDocComment();
            preg_match_all($this->phpDocRegex, $methodData, $matches, PREG_SET_ORDER, 0);
            foreach ($matches as $match) {
                $property = $match["property"];
                $values = str_getcsv($match["values"], ' ');
                if (is_array($values) && count($values) == 1)
                    $values = $values[0];
                $method->$property = $values;
            }

            if (!isset($method->Path))
                $method->Path = $controller->Path . '/' . lcfirst($phpMethod->getName());

            if (is_array($method->Path)) {
                foreach ($method->Path as $item) {
                    $tempPath = str_replace('${Controller}', $controller->Path, $item);
                    $routes[$tempPath] = $method;
                }
            } else {
                $method->Path = str_replace('${Controller}', $controller->Path, $method->Path);
                $routes[$method->Path] = $method;
            }

        }
        return $routes;
    }

    /**
     * Add a middleware handler to the router
     *
     * @param HttpMiddlewareInterface $middleware
     * @return void
     */
    public function addHandler
    (
        $middleware
    )
    {
        $this->middlewareDispatcher = $this->middlewareDispatcher->addMiddleware($middleware);
    }

    /**
     * Remove a middleware handler from the router
     *
     * @param HttpMiddlewareInterface $middleware
     * @return void
     */
    public function removeHandler
    (
        $middleware
    )
    {
        $this->middlewareDispatcher = $this->middlewareDispatcher->removeMiddleware($middleware);
    }

    /**
     * Get all registered middleware handlers
     *
     * @return HttpMiddlewareInterface[]
     */
    public function getHandlers()
    {
        return $this->middlewareDispatcher->toArray();
    }

    /**
     * Add URI to router
     *
     * @param UriInterface|string $uri
     * @param \Closure $handler
     * @return void
     */
    public function addRoute
    (
        $uri,
        $handler
    )
    {
        if ($uri instanceof UriInterface) {
            $uri = $uri->getPath();
        }
        $this->tempRouteMap[$uri] = $handler;
    }

    /**
     * Remove URI from router
     *
     * @param UriInterface|string $uri
     * @return void
     */
    public function removeRoute
    (
        $uri
    )
    {
        if ($uri instanceof UriInterface) {
            $uri = $uri->getPath();
        }
        if (array_key_exists($uri, $this->tempRouteMap)) {
            unset($this->tempRouteMap[$uri]);
        }
    }

    /**
     * Dispatch ServerRequest through all handlers
     *
     * @param ServerRequestInterface $serverRequest
     * @return ResponseInterface
     */
    public function dispatch
    (
        $serverRequest
    )
    {
        $stream = new Stream
        (
            fopen
            (
                "php://temp",
                "r+"
            )
        );
        $response = new Response
        (
            $stream,
            [],
            $this->getRouteInformation($serverRequest->getUri())
        );

        /**
         * @var $app AppInterface
         */
        $app = new App;
        $app->startup($serverRequest);

        $response = $this->middlewareDispatcher->dispatch($serverRequest, $response, $this->resolveRoute($serverRequest->getUri()));

        $response = $app->shutdown($serverRequest, $response);

        return $response;
    }

    /**
     * Get route information from route map
     *
     * @param $uri
     * @return null|array
     */
    public function getRouteInformation
    (
        $uri
    )
    {
        if ($uri instanceof UriInterface) {
            $uri = $uri->getPath();
        }
        $path = strtolower($uri);
        $array = explode("/", $path);
        foreach ($array as $key) {
            $checkPath = join("/", $array);
            if (array_key_exists($checkPath, $this->routeMap->get("routes", []))) {
                return $this->routeMap->getPath("routes")[$checkPath];
            }
            array_pop($array);
        }
        return [];
    }

    /**
     * Resolve URI to responsible handler
     *
     * @param UriInterface|string $uri
     * @return \Closure
     */
    public function resolveRoute
    (
        $uri
    )
    {
        if ($uri instanceof UriInterface) {
            $uri = $uri->getPath();
        }
        $path = strtolower($uri);
        $array = explode("/", $path);
        foreach ($array as $key) {
            $checkPath = join("/", $array);
            if ($this->hasRoute(strtolower($checkPath))) {

                if (array_key_exists($checkPath, $this->tempRouteMap)) {
                    $uri = str_replace($checkPath, '', $uri);
                    $path = explode('/', $uri);
                    unset($path[0]);
                    return function ($request, $response) use ($checkPath, $path) {
                        array_unshift($path, $response);
                        array_unshift($path, $request);
                        return call_user_func_array($this->tempRouteMap[$checkPath], $path);
                    };
                }
                $route = $this->routeMap->get("routes")[$checkPath];

                return $this->generateClosure($uri, $route);
            }
            array_pop($array);
        }

        return function ($request, $response) {
            return $response;
        };
    }

    /**
     * Check if router knows about this URI
     *
     * @param $uri UriInterface|string
     * @return bool
     */
    public function hasRoute
    (
        $uri
    )
    {
        if ($uri instanceof UriInterface) {
            $uri = strtolower($uri->getPath());
        }
        return array_key_exists($uri, $this->routeMap->get("routes", [])) || array_key_exists($uri, $this->tempRouteMap);
    }

    /**
     * @param UriInterface|string $uri
     * @param ScalarArray $route
     * @return \Closure
     */
    private function generateClosure($uri, $route)
    {
        if ($uri instanceof UriInterface) {
            $uri = $uri->getPath();
        }

        $uri = strtolower($uri);

        $uri = str_replace($route['Path'], '', $uri);
        $path = explode('/', $uri);
        unset($path[0]);
        /**
         * @param $request
         * @param ResponseInterface $response
         * @return mixed
         */
        return function ($request, $response) use ($path) {
            array_unshift($path, $response);
            array_unshift($path, $request);
            $controllerName = $response->getCustomArgument('Controller');
            $functionName = $response->getCustomArgument('Function');
            $controller = new $controllerName($request);
            return call_user_func_array(array($controller, $functionName), $path);
        };
    }


}