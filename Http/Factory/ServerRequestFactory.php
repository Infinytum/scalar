<?php
/**
 * (C) 2017 by Michael Teuscher (mk.teuscher@gmail.com)
 * as part of the Scalar PHP framework
 *
 * Released under the AGPL v3.0 license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Created by PhpStorm.
 * User: nila
 * Date: 06.06.17
 * Time: 19:07
 */

namespace Scalar\Http\Factory;


use Scalar\Http\Message\ServerRequest;
use Scalar\Http\Message\ServerRequestInterface;
use Scalar\IO\Factory\StreamFactory;
use Scalar\IO\Factory\UriFactory;
use Scalar\IO\UriInterface;

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
        $serverRequest = new ServerRequest($method, $uri, $headers, $protocol, $body, $server);
        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withParsedBody($_POST)
            ->withQueryParams($_GET)
            ->withUploadedFiles($uploadedFileFactory->createUploadedFileFromArray($_FILES));
    }
}