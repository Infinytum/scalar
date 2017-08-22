<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 06.06.17
 * Time: 19:00
 */

namespace Scalar\Http\Factory;


use Scalar\Http\Message\Request;
use Scalar\Http\Message\RequestInterface;
use Scalar\IO\Factory\UriFactory;
use Scalar\IO\UriInterface;

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