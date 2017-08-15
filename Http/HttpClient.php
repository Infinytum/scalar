<?php

namespace Scaly\Http;


use Scaly\Http\Client\HttpClientInterface;
use Scaly\Http\Message\ResponseInterface;
use Scaly\Http\Message\ServerRequestInterface;

class HttpClient
{

    /**
     * @var HttpClientInterface
     */
    private $httpClientImplementation;

    /**
     * CurlHttpClient constructor.
     * @param HttpClientInterface $httpClientImplementation
     */
    public function __construct($httpClientImplementation)
    {
        $this->httpClientImplementation = $httpClientImplementation;
    }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClientImplementation()
    {
        return $this->httpClientImplementation;
    }

    /**
     * @param HttpClientInterface $httpClientImplementation
     */
    public function setHttpClientImplementation($httpClientImplementation)
    {
        $this->httpClientImplementation = $httpClientImplementation;
    }

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
        $this->httpClientImplementation->setRequest($serverRequest);
    }


    /**
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->httpClientImplementation->getResponse();
    }

    /**
     * Execute request to remote
     *
     * @return bool
     */
    public function request()
    {
        return $this->httpClientImplementation->request();
    }

    /**
     * Get response after execution
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->httpClientImplementation->getResponse();
    }
}