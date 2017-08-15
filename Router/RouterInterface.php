<?php

namespace Scaly\Router;

use Scaly\Http\Message\ResponseInterface;
use Scaly\Http\Message\ServerRequestInterface;
use Scaly\Http\Middleware\HttpMiddlewareInterface;
use Scaly\IO\UriInterface;

interface RouterInterface
{

    /**
     * Add a middleware handler to the router
     *
     * @param HttpMiddlewareInterface $middleware
     * @return void
     */
    public function addHandler
    (
        $middleware
    );

    /**
     * Remove a middleware handler from the router
     *
     * @param HttpMiddlewareInterface $middleware
     * @return void
     */
    public function removeHandler
    (
        $middleware
    );

    /**
     * Get all registered middleware handlers
     *
     * @return HttpMiddlewareInterface[]
     */
    public function getHandlers();

    /**
     * Check if router knows about this URI
     *
     * @param $uri UriInterface|string
     * @return bool
     */
    public function hasRoute
    (
        $uri
    );

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
    );

    /**
     * Remove URI from router
     *
     * @param UriInterface|string $uri
     * @return void
     */
    public function removeRoute
    (
        $uri
    );

    /**
     * Resolve URI to responsible handler
     *
     * @param UriInterface|string $uri
     * @return \Closure
     */
    public function resolveRoute
    (
        $uri
    );

    /**
     * Dispatch ServerRequest through all handlers
     *
     * @param ServerRequestInterface $serverRequest
     * @return ResponseInterface
     */
    public function dispatch
    (
        $serverRequest
    );

}