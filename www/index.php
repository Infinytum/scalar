<?php

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

#endregion

include SCALAR_CORE . '/Core/Scalar.php';

use Scalar\Core\Scalar;

error_reporting(E_ALL);

$scalar = Scalar::getInstance();
$scalar->initialize();

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

