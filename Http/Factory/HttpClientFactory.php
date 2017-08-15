<?php

namespace Scaly\Http\Factory;

use Scaly\Http\Client\HttpClientInterface;
use Scaly\Http\HttpClient;
use Scaly\IO\UriInterface;

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