<?php

namespace Scalar\Http\Middleware;


use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;

class HttpMiddlewareDispatcher implements HttpMiddlewareDispatcherInterface
{

    /**
     * @var HttpMiddlewareInterface[]
     */
    private $middlewareLayers = [];

    /**
     * HttpMiddlewareDispatcherInterface constructor.
     *
     * @param $middleware HttpMiddlewareInterface[]
     */
    public function __construct($middleware)
    {
        $this->middlewareLayers = $middleware;
    }

    /**
     * Add a middleware handler to the dispatcher
     *
     * @param $middleware HttpMiddlewareInterface
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
     * @param $middleware HttpMiddlewareInterface
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
     * @param ServerRequestInterface $request Any object
     * @param ResponseInterface $response Any object
     * @param $core \Closure The core function to execute between middleware
     * @return mixed
     */
    public function dispatch
    (
        $request,
        $response,
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

        return $middleware($request, $response);
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
        return function ($request, $response) use ($core) {
            return $core($request, $response);
        };
    }

    /**
     * Create a middleware layer
     *
     * @param $nextMiddleware HttpMiddlewareInterface
     * @param $middleware HttpMiddlewareInterface
     * @return \Closure
     */
    function createMiddlewareLayer
    (
        $nextMiddleware,
        $middleware
    )
    {
        return function ($request, $response) use ($nextMiddleware, $middleware) {
            return $middleware->process($request, $response, $nextMiddleware);
        };
    }

    /**
     * Return all middleware as array
     *
     * @return HttpMiddlewareInterface[]
     */
    public function toArray()
    {
        return $this->middlewareLayers;
    }
}