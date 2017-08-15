<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 06.06.17
 * Time: 19:00
 */

namespace Scaly\Http\Factory;


use Scaly\Http\Message\Request;
use Scaly\Http\Message\RequestInterface;
use Scaly\IO\Factory\UriFactory;
use Scaly\IO\UriInterface;

class RequestFactory implements RequestFactoryInterface
{

    /**
     * Create a new request.
     *
     * @param string $method
     * @param UriInterface|string $uri
     * @return RequestInterface
     */
    public function createRequest($uri, $method = "GET")
    {
        if (is_string($uri)) {
            $uriFactory = new UriFactory();
            $uri = $uriFactory->createUri($uri);
        }
        return new Request($method, $uri);
    }
}