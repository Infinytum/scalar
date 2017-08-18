<?php

namespace Scalar\Core\Middleware;


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