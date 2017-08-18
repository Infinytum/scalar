<?php

namespace Scalar\Http\Factory;


use Scalar\Http\Message\RequestInterface;
use Scalar\IO\UriInterface;

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