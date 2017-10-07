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


use Scalar\Util\ScalarArray;

/**
 * Class RoutingTable
 *
 * Represents the routing array for the router
 *
 * @package Scalar\Core\Router
 */
class RoutingTable
{

    const EXCEPTION_MSG_INVALID_ROUTE_ARRAY = 'Invalid route array passed to RoutingTable constructor!';

    /**
     * Dynamic or static routes
     * @var ScalarArray
     */
    private $routes;

    /**
     * Programmatically defined routes which can override the routing table
     * @var ScalarArray
     */
    private $staticRoutes;

    /**
     * Route which will be returned if requested route is not present in the routing table
     * @var RouteEntry
     */
    private $defaultRoute;

    /**
     * RoutingTable constructor.
     *
     * @param ScalarArray|array $routes
     * @param ScalarArray|array $staticRoutes
     * @param null|RouteEntry $defaultRoute
     * @throws \Exception
     */
    public function __construct
    (
        $routes = [],
        $staticRoutes = [],
        $defaultRoute = null
    )
    {
        if (!$routes instanceof ScalarArray && is_array($routes)) {
            $routes = new ScalarArray($routes);
        } else {
            throw new \Exception(self::EXCEPTION_MSG_INVALID_ROUTE_ARRAY);
        }

        if (!$staticRoutes instanceof ScalarArray && is_array($staticRoutes)) {
            $staticRoutes = new ScalarArray($staticRoutes);
        } else {
            throw new \Exception(self::EXCEPTION_MSG_INVALID_ROUTE_ARRAY);
        }

        $this->routes = $routes;
        $this->staticRoutes = $staticRoutes;
        $this->defaultRoute = $defaultRoute;
    }

    /**
     * Add a new route to the routing table
     *
     * @param RouteEntry $route
     * @return $this
     */
    public function addRoute
    (
        $route
    )
    {
        if ($route->isStatic()) {
            $this->staticRoutes->set($route->getRoute(), $route);
        } else {
            $this->routes->set($route->getRoute(), $route);
        }

        return $this;
    }

    /**
     * Resolve route in the entire routing table.
     * This will check both routing tables for the given route
     *
     * @param string $route Unique string which will be resolved from the entire routing table
     * @return RouteEntry|null Returns the default route if requested route was not present
     */
    public function resolveRoute
    (
        $route
    )
    {
        return $this->hasRoute($route) ? $this->getRoute($route) : $this->getRoute($route, true);
    }

    /**
     * Check if a route is present in the current routing table
     *
     * @param string $route Unique string which will be checked against the routing table
     * @param bool $static When set to true, this route will be checked against the static routing table
     * @return bool
     */
    public function hasRoute
    (
        $route,
        $static = false
    )
    {
        return $this->getRoute($route, $static) !== null;
    }

    /**
     * Get handler for a registered route
     *
     * @param string $route Unique string which will be used to fetch handler from the routing table
     * @param bool $static When set to true, this route will be fetched from the static routing table
     * @return RouteEntry|null Returns default route if requested route was not present
     */
    public function getRoute
    (
        $route,
        $static = false
    )
    {
        if ($static) {
            $routeData = $this->staticRoutes->get($route, $this->defaultRoute);
        } else {
            $routeData = $this->routes->get($route, $this->defaultRoute);
        }

        if (is_array($routeData)) {
            $routeObject = new RouteEntry
            (
                $route,
                isset($routeData['Handler']) ? $routeData['Handler'] : null,
                $static,
                isset($routeData['Data']) ? new ScalarArray($routeData['Data']) : new ScalarArray()
            );
            return $routeObject;
        }
        return $routeData;
    }

    /**
     * Remove an existing route from the routing table
     *
     * @param string $route Unique string which will be removed from the routing table
     * @param bool $static When set to true, this route will be removed from the static routing table
     * @return $this
     */
    public function removeRoute
    (
        $route,
        $static = false
    )
    {
        if ($static) {
            unset($this->staticRoutes[$route]);
        } else {
            unset($this->routes[$route]);
        }

        return $this;
    }

    /**
     * Returns a copy of the current routing table
     * @return ScalarArray
     */
    public function getRoutingTable()
    {
        return clone $this->routes;
    }

    /**
     * Returns a copy of the current static routing table
     * @return ScalarArray
     */
    public function getStaticRoutingTable()
    {
        return clone $this->staticRoutes;
    }
}