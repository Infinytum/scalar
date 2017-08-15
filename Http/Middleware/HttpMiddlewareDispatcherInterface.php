<?php

namespace Scaly\Http\Middleware;


use Scaly\Http\Message\ResponseInterface;
use Scaly\Http\Message\ServerRequestInterface;

interface HttpMiddlewareDispatcherInterface
{

    /**
     * HttpMiddlewareDispatcherInterface constructor.
     *
     * @param $middleware HttpMiddlewareInterface[]
     */
    public function __construct($middleware);

    /**
     * Add a middleware handler to the dispatcher
     *
     * @param $middleware HttpMiddlewareInterface
     * @return static
     */
    public function addMiddleware
    (
        $middleware
    );

    /**
     * Remove a middleware handler from the dispatcher
     *
     * @param $middleware HttpMiddlewareInterface
     * @return static
     */
    public function removeMiddleware
    (
        $middleware
    );

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
    );

    /**
     * Return all middleware as array
     *
     * @return HttpMiddlewareInterface[]
     */
    public function toArray();

    /**
     * Create core function
     *
     * @param $core \Closure The actual Core function
     * @return \Closure
     */
    function createCore
    (
        $core
    );

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
    );

}