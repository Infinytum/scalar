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

class RouteEntry
{

    /**
     * Unique route identifier
     * @var string
     */
    private $route;

    /**
     * Determines whether this route is static or not
     * @var bool
     */
    private $static;

    /**
     * Custom data which is provided for this route
     * @var ScalarArray
     */
    private $data;

    /**
     * Handler which will is responsible for this route
     * @var \Closure
     */
    private $handler;

    /**
     * RouteEntry constructor.
     * @param string $route
     * @param \Closure $handler
     * @param bool $static
     * @param ScalarArray|array $data
     */
    public function __construct
    (
        $route,
        $handler,
        $static = false,
        $data = null
    )
    {
        $this->route = $route;
        $this->handler = $handler;
        $this->static = $static;
        if (is_array($data)) {
            $data = new ScalarArray($data);
        }
        if ($data === null) {
            $data = new ScalarArray();
        }
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param string $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @return bool
     */
    public function isStatic()
    {
        return $this->static;
    }

    /**
     * @param bool $static
     */
    public function setStatic($static)
    {
        $this->static = $static;
    }

    /**
     * @return ScalarArray
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param ScalarArray $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return \Closure
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param \Closure $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

}