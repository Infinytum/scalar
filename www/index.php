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

define('SCALAR_CORE', dirname(getcwd()));

#region PHP Patches

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

umask(0);

#endregion

include SCALAR_CORE . '/Core/Scalar.php';

use Scalar\Core\Scalar;


$scalar = Scalar::getInstance();
$scalar->initialize();

if (!Scalar::isDeveloperMode()) {
    error_reporting(0);
}


/**
 * @var \Scalar\Core\Router\CoreRouter $router
 */
$router = Scalar::getService
(
    Scalar::SERVICE_ROUTER
);

$serverRequestFactory = new \Scalar\Http\Factory\ServerRequestFactory();
$serverRequest = $serverRequestFactory->createServerRequestFromArray($_SERVER);

$response = $router->dispatch($serverRequest);

if (!$response) {
    $responseFactory = new \Scalar\Http\Factory\ResponseFactory();
    $response = $responseFactory->createResponse(404);
}

$response->getBody()->rewind();


header(
    'HTTP/' .
    $response->getProtocolVersion() . ' ' .
    $response->getStatusCode() . ' ' .
    $response->getReasonPhrase());

foreach ($response->getHeaders() as $headerName => $headerValue) {
    header($response->getHeaderLine($headerName));
}

echo $response->getBody()->getContents();

