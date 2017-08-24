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

use Scalar\Http\Exception\InvalidProtocolVersionException;
use Scalar\IO\Stream\Stream;
use Scalar\IO\Stream\StreamInterface;


class Message implements MessageInterface
{

    private $protocolVersion;
    private $headers;
    private $bodyStream;

    public function __construct($protocol, $headers, $body)
    {
        $this->protocolVersion = $protocol;
        $this->headers = $headers;

        if ($body === null || $body instanceof StreamInterface)
            $this->bodyStream = $body;
    }

    /**
     * Get protocol version
     * @return string protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * Get instance with protocol version
     * @param string $version protocol version
     * @return static
     * @throws InvalidProtocolVersionException
     */
    public function withProtocolVersion($version) // TODO: Throw if invalid
    {
        $newInstance = clone $this;
        $newInstance->protocolVersion = $version;
        return $newInstance;
    }

    /**
     * Get all headers
     * @return string[][] Returns an associative array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get header value as concatenated string
     * @param string $name header name.
     * @return string Concatenated header value
     */
    public function getHeaderLine($name)
    {
        if (!$this->hasHeader($name)) {
            return '';
        }
        return join(',', $this->getHeader($name));
    }

    /**
     * Check if header is present
     * @param string $name header name.
     * @return bool
     */
    public function hasHeader($name)
    {
        return array_key_exists($name, $this->headers);
    }

    /**
     * Get header values as array
     * @param string $name header name.
     * @return string[] An array of string values
     */
    public function getHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return array();
        }
        return $this->headers[$name];
    }

    /**
     * Get message with added header
     * @param string $name header to add.
     * @param string|string[] $value Header value.
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        $current = array();
        if ($this->hasHeader($name)) {
            $current = $this->getHeader($name);
        }
        $updated = array_merge($current, $value);
        return $this->withHeader($name, $updated);
    }

    /**
     * Get instance with header
     * @param string $name header name.
     * @param string|string[] $value Header value.
     * @return static
     */
    public function withHeader($name, $value)
    {
        $newInstance = clone $this;
        if (!is_array($value)) {
            $value = array($value);
        }
        unset($newInstance->headers[$name]);
        $newInstance->headers = [$name => $value] + $newInstance->headers;
        return $newInstance;
    }

    /**
     * Get message without header
     * @param string $name header to remove.
     * @return static
     */
    public function withoutHeader($name)
    {
        $newInstance = clone $this;
        if ($this->hasHeader($name)) {
            unset($newInstance->headers[$name]);
        }
        return $newInstance;
    }

    /**
     * Get body of message
     *
     * @return StreamInterface Message body
     */
    public function getBody()
    {
        return $this->bodyStream;
    }

    /**
     * Get message with body
     * @param StreamInterface $body Body.
     * @return static
     */
    public function withBody($body)
    {
        $newInstance = clone $this;
        $stream = new Stream(fopen("php://temp", "w+"));
        $stream->write($body->getContents());
        $newInstance->bodyStream = $stream;
        return $newInstance;
    }
}