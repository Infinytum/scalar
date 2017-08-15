<?php

namespace Scaly\Http\Factory;


use Scaly\Http\Message\RequestInterface;
use Scaly\IO\UriInterface;

interface RequestFactoryInterface
{

    /**
     * Create a new request.
     *
     * @param string $method
     * @param UriInterface|string $uri
     * @return RequestInterface
     */
    public function createRequest($uri, $method = "GET");

}