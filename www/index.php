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

if (!function_exists('extract_namespace')) {
    function extract_namespace($file)
    {
        $ns = NULL;
        $handle = fopen($file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (strpos($line, 'namespace') === 0) {
                    $parts = explode(' ', $line);
                    $ns = rtrim(trim($parts[1]), ';');
                    break;
                }
            }
            fclose($handle);
        }
        return $ns;
    }
}

umask(0);

#endregion

include SCALAR_CORE . '/Core/Scalar.php';

use Scalar\Core\Scalar;


$scalar = Scalar::getInstance();

$serverRequestFactory = new \Scalar\Http\Factory\ServerRequestFactory();
$serverRequest = $serverRequestFactory->createServerRequestFromArray($_SERVER);

$scalar->startup($serverRequest);

if (!Scalar::isDeveloperMode()) {
    error_reporting(0);
}


/**
 * @var \Scalar\Core\Service\CoreRouterService $router
 */
$router = Scalar::getService
(
    Scalar::SERVICE_CORE_ROUTER
);


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

$scalar->shutdown($serverRequest, $response);
