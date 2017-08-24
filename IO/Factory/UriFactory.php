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
 * Time: 18:26
 */

namespace Scalar\IO\Factory;


use Scalar\IO\Exception\MalformedUriException;
use Scalar\IO\Uri;
use Scalar\IO\UriInterface;

class UriFactory implements UriFactoryInterface
{

    /**
     * Create a new URI.
     *
     * @param string $uri
     * @return UriInterface
     * @throws MalformedUriException If URI is invalid
     */
    public function createUri($uri = '')
    {
        $uriInstance = new Uri();
        return $uriInstance->fromString($uri);
    }


    /**
     * Create a new uri from $_SERVER
     *
     * @param array $server $_SERVER or similar
     * @return UriInterface
     */
    public function createUriFromArray
    (
        $server
    )
    {
        $uri = new Uri(!empty($server['HTTPS']) && $server['HTTPS'] !== 'off' ? 'https' : 'http');
        $hasPort = false;

        if (isset($server['HTTP_HOST'])) {
            $hostHeaderParts = explode(':', $server['HTTP_HOST']);
            $uri = $uri->withHost($hostHeaderParts[0]);
            if (isset($hostHeaderParts[1])) {
                $hasPort = true;
                $uri = $uri->withPort($hostHeaderParts[1]);
            }
        }

        if (!$hasPort && isset($server['SERVER_PORT'])) {
            $uri = $uri->withPort($server['SERVER_PORT']);
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = explode('?', $_SERVER['REQUEST_URI']);
            $uri = $uri->withPath($requestUri[0]);
            if (isset($requestUri[1])) {
                $uri = $uri->withQuery($requestUri[1]);
            }
        }
        return $uri;
    }
}