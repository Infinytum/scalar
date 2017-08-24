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

namespace Scalar\Router;

use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;
use Scalar\Http\Middleware\HttpMiddlewareInterface;
use Scalar\IO\UriInterface;

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