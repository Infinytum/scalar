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

namespace Scalar\Http\Message;


use InvalidArgumentException;
use Scalar\IO\Uri;
use Scalar\IO\UriInterface;

class Request extends Message implements RequestInterface
{

    /**
     * @var string
     */
    private $requestMethod;

    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var string
     */
    private $requestTarget;

    public function __construct($method, $uri, $headers = [], $version = "1.0", $body = null)
    {
        parent::__construct($version, $headers, $body);
        $this->requestMethod = $method;
        $this->uri = $uri;
    }

    /**
     * Get request target
     * @return string
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget)
            return $this->requestTarget;
        $target = '/';
        if ($path = $this->uri->getPath()) {
            $target = $path;
        }
        if ($query = $this->uri->getQuery()) {
            $target = '?' . $query;
        }
        return $target;
    }

    /**
     * Get instance with request target
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        $newInstance = clone $this;
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Target cannot contain any whitespaces!'
            );
        }
        $newInstance->requestTarget = $requestTarget;
        return $newInstance;
    }

    /**
     * Get request method
     * @return string
     */
    public function getMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Get instance with request method
     * @param string $method
     * @return static
     */
    public function withMethod($method)
    {
        $newInstance = clone $this;
        $newInstance->requestMethod = $method;
        return $newInstance;
    }

    /**
     * Get request URI
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get instance with URI
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return static
     */
    public function withUri($uri, $preserveHost = false)
    {
        $newInstance = clone $this;
        $newInstance->uri = $uri;

        if (!$preserveHost) {
            $host = $newInstance->uri->getHost();
            if ($port = $newInstance->uri->getPort())
                $host .= ':' . $port;
            if ($newInstance->hasHeader("Host"))
                $newInstance = $newInstance->withHeader("Host", $host);
        }
        return $newInstance;
    }
}