<?php

namespace Scalar\Http\Factory;

use Scalar\Http\Client\HttpClientInterface;
use Scalar\Http\HttpClient;
use Scalar\IO\UriInterface;

class HttpClientFactory
{

    /**
     * @param HttpClientInterface $implementation
     * @param UriInterface $uri
     * @return HttpClient
     */
    public function createHttpClient
    (
        $implementation,
        $uri
    )
    {
        $serverRequestFactory = new ServerRequestFactory();

        $httpClient = new HttpClient($implementation);
        $httpClient->setRequest($serverRequestFactory->createServerRequest("GET", $uri));
        return $httpClient;
    }

}