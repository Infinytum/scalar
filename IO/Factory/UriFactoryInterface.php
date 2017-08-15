<?php

namespace Scaly\IO\Factory;

use Scaly\IO\Exception\MalformedUriException;
use Scaly\IO\UriInterface;

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