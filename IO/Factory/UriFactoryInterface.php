<?php

namespace Scalar\IO\Factory;

use Scalar\IO\Exception\MalformedUriException;
use Scalar\IO\UriInterface;

interface UriFactoryInterface
{

    /**
     * Create a new URI.
     *
     * @param string $uri
     * @return UriInterface
     * @throws MalformedUriException If URI is invalid
     */
    public function createUri($uri = '');

}