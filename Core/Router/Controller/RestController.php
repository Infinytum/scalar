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

namespace Scalar\Core\Router\Controller;

use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;

/**
 * Interface RestController
 * @package Scalar\Router\Controller
 *
 * Template for REST controllers
 */
interface RestController
{

    /**
     * REST API GET method
     *
     * @Path ${Controller}
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $id Unique object identifier
     * @return ResponseInterface Returns array of objects if no id is specified, else return object with id
     */
    public function get
    (
        $request,
        $response,
        $id = null
    );

    /**
     * REST API POST method
     *
     * @Path ${Controller}
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface Returns created object with it's new unique id
     */
    public function create
    (
        $request,
        $response
    );

    /**
     * REST API PUT
     *
     * @Path ${Controller}
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $id Unique object identifier
     * @return ResponseInterface Returns updated object
     */
    public function update
    (
        $request,
        $response,
        $id = null
    );

    /**
     * REST API PATCH
     *
     * @Path ${Controller}
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $id Unique object identifier
     * @return ResponseInterface Returns updated object
     */
    public function patch
    (
        $request,
        $response,
        $id = null
    );

    /**
     * REST API DELETE
     *
     * @Path ${Controller}
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $id Unique object identifier
     * @return ResponseInterface Returns success or failure
     */
    public function delete
    (
        $request,
        $response,
        $id = null
    );

}