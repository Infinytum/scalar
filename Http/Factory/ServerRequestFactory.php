<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 06.06.17
 * Time: 19:07
 */

namespace Scaly\Http\Factory;


use Scaly\Http\Message\ServerRequest;
use Scaly\Http\Message\ServerRequestInterface;
use Scaly\IO\Factory\StreamFactory;
use Scaly\IO\Factory\UriFactory;
use Scaly\IO\UriInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{

    /**
     * Create a new server request.
     *
     * @param string $method
     * @param UriInterface|string $uri
     * @return ServerRequestInterface
     */
    public function createServerRequest($method, $uri)
    {
        if (is_string($uri)) {
            $uriFactory = new UriFactory();
            $uri = $uriFactory->createUri($uri);
        }
        return new ServerRequest($method, $uri);
    }

    /**
     * Create a new server request from $_SERVER.
     *
     * @param array $server $_SERVER or similar
     * @return ServerRequestInterface
     * @throws \InvalidArgumentException If detection of method or URI fails
     */
    public function createServerRequestFromArray(array $server)
    {
        $streamFactory = new StreamFactory();
        $uploadedFileFactory = new UploadedFileFactory();
        $uriFactory = new UriFactory();

        $method = isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
        $headers = getallheaders();
        $uri = $uriFactory->createUriFromArray($server);

        $prereadBody = $streamFactory->createStreamFromFile("php://input", "r");
        $body = $streamFactory->createStreamFromFile('php://temp', 'r+');
        $body->write($prereadBody->getContents());
        $body->rewind();

        $protocol = isset($server['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $server['SERVER_PROTOCOL']) : '1.0';
        $serverRequest = new ServerRequest($method, $uri, $headers, $body, $protocol, $server);
        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withParsedBody($_POST)
            ->withQueryParams($_GET)
            ->withUploadedFiles($uploadedFileFactory->createUploadedFileFromArray($_FILES));
    }
}