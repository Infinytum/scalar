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

namespace Scalar\Http\Client;

use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;

interface HttpClientInterface
{

    /**
     * Set request to execute
     *
     * @param $serverRequest ServerRequestInterface
     * @return void
     */
    public function setRequest
    (
        $serverRequest
    );


    /**
     * Get request
     *
     * @return ServerRequestInterface
     */
    public function getRequest();

    /**
     * Execute request to remote
     *
     * @return mixed
     */
    public function request();


    /**
     * Get response after execution
     *
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * Check if implementation is currently available
     *
     * @return bool
     */
    public function isAvailable();

}