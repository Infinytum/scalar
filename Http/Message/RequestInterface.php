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

use Scalar\IO\UriInterface;

/**
 * Interface RequestInterface
 *
 * Abstract HTTP request implementation
 *
 * @package Scalar\Http\Message
 */
interface RequestInterface extends MessageInterface
{
    /**
     * Get request target
     * @return string
     */
    public function getRequestTarget();

    /**
     * Get instance with request target
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget);

    /**
     * Get request method
     * @return string
     */
    public function getMethod();

    /**
     * Get instance with request method
     * @param string $method
     * @return static
     */
    public function withMethod($method);

    /**
     * Get request URI
     * @return UriInterface
     */
    public function getUri();

    /**
     * Get instance with URI
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return static
     */
    public function withUri($uri, $preserveHost = false);
}