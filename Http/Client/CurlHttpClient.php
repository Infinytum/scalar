<?php

namespace Scaly\Http\Client;


use Scaly\Http\Factory\ResponseFactory;
use Scaly\Http\Message\ResponseInterface;
use Scaly\Http\Message\ServerRequestInterface;
use Scaly\IO\Factory\StreamFactory;

class CurlHttpClient implements HttpClientInterface
{

    /**
     * @var ServerRequestInterface
     */
    private $serverRequest;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * Set request to execute
     *
     * @param $serverRequest ServerRequestInterface
     * @return void
     */
    public function setRequest
    (
        $serverRequest
    )
    {
        $this->serverRequest = $serverRequest;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->serverRequest;
    }

    /**
     * Execute request to remote
     *
     * @return bool
     */
    public function request()
    {
        $curl = curl_init($this->serverRequest->getUri());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if ($this->serverRequest->getMethod() == "POST") {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->serverRequest->getQueryParams());
        }

        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $responseFactory = new ResponseFactory();
        $streamFactory = new StreamFactory();
        $this->response = $responseFactory->createResponse($httpCode);
        $this->response = $this->response->withBody($streamFactory->createStream($result));
        $this->response->getBody()->rewind();

        return $result != false;
    }

    /**
     * Get response after execution
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Check if implementation is currently available
     *
     * @return bool
     */
    public function isAvailable()
    {
        return function_exists('curl_init');
    }
}