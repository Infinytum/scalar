<?php

namespace Scaly\Core\Middleware;


interface MiddlewareDispatcherInterface
{

    /**
     * HttpMiddlewareDispatcherInterface constructor.
     *
     * @param $middleware MiddlewareInterface[]
     */
    public function __construct($middleware);

    /**
     * Add a middleware handler to the dispatcher
     *
     * @param $middleware MiddlewareInterface
     * @return static
     */
    public function addMiddleware
    (
        $middleware
    );

    /**
     * Remove a middleware handler from the dispatcher
     *
     * @param $middleware MiddlewareInterface
     * @return static
     */
    public function removeMiddleware
    (
        $middleware
    );

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
    );

    /**
     * Return all middleware as array
     *
     * @return MiddlewareInterface[]
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
     * @param $nextMiddleware MiddlewareInterface
     * @param $middleware MiddlewareInterface
     * @return \Closure
     */
    function createMiddlewareLayer
    (
        $nextMiddleware,
        $middleware
    );

}