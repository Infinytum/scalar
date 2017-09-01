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

/**
 * Created by PhpStorm.
 * User: nila
 * Date: 8/19/17
 * Time: 7:10 PM
 */

namespace Scalar\App;

use Scalar\Http\Message\RequestInterface;
use Scalar\Http\Message\ResponseInterface;
use Scalar\Router\AppInterface;

class App implements AppInterface
{

    /**
     * This function is being executed before the request is dispatched
     *
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function startup
    (
        $request
    )
    {

        return $request;
    }

    /**
     * This function is being executed after the request has been dispatched
     * and the response is ready to be returned to the client
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function shutdown
    (
        $request,
        $response
    )
    {

        return $response;
    }
}