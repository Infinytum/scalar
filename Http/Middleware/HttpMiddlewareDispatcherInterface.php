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

namespace Scalar\Http\Middleware;


use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;

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