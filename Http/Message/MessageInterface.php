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

use Scalar\IO\Stream\StreamInterface;

/**
 * Interface MessageInterface
 *
 * Abstract HTTP protocol implementation
 *
 * @package Scalar\Http\Message
 */
interface MessageInterface
{
    /**
     * Get protocol version
     * @return string protocol version.
     */
    public function getProtocolVersion();

    /**
     * Get instance with protocol version
     * @param string $version protocol version
     * @return static
     */
    public function withProtocolVersion($version);

    /**
     * Get all headers
     * @return string[][] Returns an associative array
     */
    public function getHeaders();

    /**
     * Check if header is present
     * @param string $name header name.
     * @return bool
     */
    public function hasHeader($name);

    /**
     * Get header values as array
     * @param string $name header name.
     * @return string[] An array of string values
     */
    public function getHeader($name);

    /**
     * Get header value as concatenated string
     * @param string $name header name.
     * @return string Concatenated header value
     */
    public function getHeaderLine($name);

    /**
     * Get instance with header
     * @param string $name header name.
     * @param string|string[] $value Header value.
     * @return static
     */
    public function withHeader($name, $value);

    /**
     * Get message with added header
     * @param string $name header to add.
     * @param string|string[] $value Header value.
     * @return static
     */
    public function withAddedHeader($name, $value);

    /**
     * Get message without header
     * @param string $name header to remove.
     * @return static
     */
    public function withoutHeader($name);

    /**
     * Get body of message
     *
     * @return StreamInterface Message body
     */
    public function getBody();

    /**
     * Get message with body
     * @param StreamInterface $body Body.
     * @return static
     */
    public function withBody($body);
}