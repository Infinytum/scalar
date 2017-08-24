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

namespace Scalar\IO;


/**
 * Interface UriInterface
 *
 * Represents a RFC conform URI implementation
 *
 * @package Scalar\Http\Message
 */
interface UriInterface extends \Serializable
{
    /**
     * Get URI Scheme
     * @return string The URI scheme.
     */
    public function getScheme();

    /**
     * Get URI authority
     * @return string The URI authority
     */
    public function getAuthority();

    /**
     * Get URI user information
     * @return string The URI user information
     */
    public function getUserInfo();

    /**
     * Get URI host
     * @return string The URI host.
     */
    public function getHost();

    /**
     * Get URI port
     * @return int|null The URI port.
     */
    public function getPort();

    /**
     * Get URI path
     * @return string The URI path.
     */
    public function getPath();

    /**
     * Get URI query string
     * @return string The URI query string.
     */
    public function getQuery();

    /**
     * Get URI fragment
     * @return string The URI fragment.
     */
    public function getFragment();

    /**
     * Get URI instance with scheme
     * @param string $scheme The scheme
     * @return static A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid schemes.
     */
    public function withScheme($scheme);

    /**
     * Get URI instance with user info
     *
     * @param string $user The username
     * @param null|string $password The password
     * @return static A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null);

    /**
     * Get URI instance with host
     * @param string $host The hostname
     * @return static A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid schemes.
     */
    public function withHost($host);

    /**
     * Get URI instance with port
     * @param null|int $port The port
     * @return static A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid schemes.
     */
    public function withPort($port);

    /**
     * Get URI instance with path
     * @param string $path The path
     * @return static A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid schemes.
     */
    public function withPath($path);

    /**
     * Get URI instance with query
     * @param string $query The query
     * @return static A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid schemes.
     */
    public function withQuery($query);

    /**
     * Get URI with fragment
     * @param string $fragment The fragment
     * @return static A new instance with the specified fragment.
     */
    public function withFragment($fragment);

    /**
     * Return URI as string
     *
     * @return string
     */
    public function __toString();
}