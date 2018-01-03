<?php
/**
 * (C) 2018 by Michael Teuscher (mk.teuscher@gmail.com)
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

namespace Scalar\Http\Client;


use Scalar\Http\Factory\ResponseFactory;
use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;
use Scalar\IO\Factory\StreamFactory;

class CurlHttpClient implements HttpClientInterface
{

    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    /**
     * @var ServerRequestInterface
     */
    private $serverRequest;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var string
     */
    private $postBody = self::CONTENT_TYPE_FORM_URLENCODED;

    /**
     * Set request to execute
     *
     * @param $serverRequest ServerRequestInterface
     * @return void
     */
    public function setRequest
    (
        $serverRequest
    )
    {
        $this->serverRequest = $serverRequest;
    }

    /**
     * Set true if data should be posted
     *
     * @param $postBody string
     * @return void
     */
    public function setPostContentType
    (
        $postBody
    )
    {
        $this->postBody = $postBody;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->serverRequest;
    }

    /**
     * Execute request to remote
     *
     * @return bool
     */
    public function request()
    {
        $curl = curl_init($this->serverRequest->getUri());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if ($this->serverRequest->getMethod() == "POST") {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($body = $this->serverRequest->getBody()) {
                $body->rewind();
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:' . $this->postBody));
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body->getContents());
            } else {
            }
        }

        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $responseFactory = new ResponseFactory();
        $streamFactory = new StreamFactory();
        $this->response = $responseFactory->createResponse($httpCode);
        $this->response = $this->response->withBody($streamFactory->createStream($result));
        $this->response->getBody()->rewind();

        return $result != false;
    }

    /**
     * Get response after execution
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Check if implementation is currently available
     *
     * @return bool
     */
    public function isAvailable()
    {
        return function_exists('curl_init');
    }
}