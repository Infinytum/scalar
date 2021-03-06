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

/**
 * Class Router
 * @package Scalar\Router
 */
class Router
{

    /**
     * Routing table
     * @var RoutingTable
     */
    private $routingTable;

    /**
     * Router constructor.
     * @param RoutingTable $routingTable
     */
    function __construct
    (
        $routingTable
    )
    {
        $this->routingTable = $routingTable;
    }

    /**
     * @param $route
     * @param $arguments
     * @return mixed
     */
    public function route
    (
        $route,
        $arguments
    )
    {
        $routeHandler = $this->routingTable->resolveRoute($route);

        if ($routeHandler === null) {
            return null;
        }

        return call_user_func_array($routeHandler, $arguments);
    }

    /**
     * Get routing table for this router
     * @return RoutingTable
     */
    public function getRoutingTable()
    {
        return $this->routingTable;
    }


}