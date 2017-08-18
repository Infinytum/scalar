<?php

namespace Scalar\Http\Factory;


use Scalar\Http\Message\ServerRequestInterface;
use Scalar\IO\UriInterface;

interface ServerRequestFactoryInterface
{

    /**
     * Create a new server request.
     *
     * @param string $method
     * @param UriInterface|string $uri
     * @return ServerRequestInterface
     */
    public function createServerRequest($method, $uri);

    /**
     * Create a new server request from $_SERVER.
     *
     * @param array $server $_SERVER or similar
     * @return ServerRequestInterface
     * @throws \InvalidArgumentException If detection of method or URI fails
     */
    public function createServerRequestFromArray(array $server);

}