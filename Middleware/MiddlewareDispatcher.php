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

namespace Scalar\Middleware;


class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewareLayers = [];

    /**
     * HttpMiddlewareDispatcherInterface constructor.
     *
     * @param $middleware MiddlewareInterface[]
     */
    public function __construct($middleware)
    {
        $this->middlewareLayers = $middleware;
    }

    /**
     * Add a middleware handler to the dispatcher
     *
     * @param $middleware MiddlewareInterface
     * @return static
     */
    public function addMiddleware
    (
        $middleware
    )
    {
        $newInstance = clone $this;

        array_push($newInstance->middlewareLayers, $middleware);
        return $newInstance;
    }

    /**
     * Remove a middleware handler from the dispatcher
     *
     * @param $middleware MiddlewareInterface
     * @return static
     */
    public function removeMiddleware
    (
        $middleware
    )
    {
        if (!in_array($middleware, $this->middlewareLayers))
            return $this;

        $newInstance = clone $this;

        if (($key = array_search($middleware, $newInstance->middlewareLayers)) !== false) {
            unset($newInstance->middlewareLayers[$key]);
        }

        return $newInstance;
    }

    /**
     *
     * @param $object mixed Any object
     * @param $core \Closure The core function to execute between middleware
     * @return mixed
     */
    public function dispatch
    (
        $object,
        $core
    )
    {
        $core = $this->createCore($core);
        $middlewareLayers = array_reverse($this->middlewareLayers);

        $middleware = array_reduce
        (
            $middlewareLayers,
            function ($nextLayer, $layer) {
                return $this->createMiddlewareLayer($nextLayer, $layer);
            },
            $core
        );

        return $middleware($object);
    }

    /**
     * Create core function
     *
     * @param $core \Closure The actual Core function
     * @return \Closure
     */
    function createCore
    (
        $core
    )
    {
        return function ($object) use ($core) {
            return $core($object);
        };
    }

    /**
     * Create a middleware layer
     *
     * @param $nextMiddleware MiddlewareInterface
     * @param $middleware MiddlewareInterface
     * @return \Closure
     */
    function createMiddlewareLayer
    (
        $nextMiddleware,
        $middleware
    )
    {
        return function ($object) use ($nextMiddleware, $middleware) {
            return $middleware->process($object, $nextMiddleware);
        };
    }

    /**
     * Return all middleware as array
     *
     * @return MiddlewareInterface[]
     */
    public function toArray()
    {
        return $this->middlewareLayers;
    }
}