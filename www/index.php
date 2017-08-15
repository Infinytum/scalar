<?php

define('SCALY_CORE', dirname(getcwd()));

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

include SCALY_CORE . '/Core/Scaly.php';

use Scaly\Core\Scaly;

error_reporting(E_ALL);

$scaly = Scaly::getInstance();
$scaly->initialize();

$serverRequestFactory = new \Scaly\Http\Factory\ServerRequestFactory();
$serverRequest = $serverRequestFactory->createServerRequestFromArray($_SERVER);

$response = Scaly::getRouter()->dispatch($serverRequest);

if (!$response) {
    $responseFactory = new \Scaly\Http\Factory\ResponseFactory();
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

