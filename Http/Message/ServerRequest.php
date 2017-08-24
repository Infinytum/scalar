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
 * Date: 18.05.17
 * Time: 23:19
 */

namespace Scalar\Http\Message;

class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $cookieParams = [];

    /**
     * @var null|array|object
     */
    private $parsedBody;

    /**
     * @var array
     */
    private $queryParams = [];

    /**
     * @var array
     */
    private $serverParams;

    /**
     * @var UploadedFileInterface[]
     */
    private $uploadedFiles = [];

    public function __construct($method, $uri, $headers = [], $version = "1.0", $body = null, $serverParams = [])
    {
        parent::__construct($method, $uri, $headers, $version, $body);
        $this->serverParams = $serverParams;
    }

    /**
     * Get instance with uploaded files
     * @param array $uploadedFiles
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $newInstance = clone $this;
        $newInstance->uploadedFiles = $uploadedFiles;
        return $newInstance;
    }

    /**
     * Get instance with query parameters
     * @param array $query
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $newInstance = clone $this;
        $newInstance->queryParams = $query;
        return $newInstance;
    }

    /**
     * Get instance with deserialized body
     * @param null|array|object
     * @return static
     */
    public function withParsedBody($data)
    {
        $newInstance = clone $this;
        $newInstance->parsedBody = $data;
        return $newInstance;
    }

    /**
     * Get instance with cookies
     * @param array $cookies
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $newInstance = clone $this;
        $newInstance->cookieParams = $cookies;
        return $newInstance;
    }

    /**
     * Get server parameters
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Get cookie parameters
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * Get query parameters
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Get uploaded files
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * Get deserialized body
     * @return null|array|object
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Get request attributes
     * @return mixed[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get single attribute
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $default;
        }
        return $this->attributes[$name];
    }

    /**
     * Get instance with added attribute
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $newInstance = clone $this;
        $newInstance->attributes[$name] = $value;
        return $newInstance;
    }

    /**
     * Get instance without attribute
     * @param string $name
     * @return static
     */
    public function withoutAttribute($name)
    {

        if (!array_key_exists($name, $this->attributes)) {
            return $this;
        }

        $newInstance = clone $this;
        unset($newInstance->attributes[$name]);

        return $newInstance;
    }

    /**
     * Get value from POST variable
     * @param string $name Variable name
     * @param mixed $default Default value
     * @return mixed Returns value or default
     */
    public function getPost($name, $default = null)
    {
        if (array_key_exists($name, $_POST)) {
            return $_POST[$name];
        }
        return $default;
    }

    /**
     * Get value from GET variable
     * @param string $name Variable name
     * @param mixed $default Default value
     * @return mixed Returns value or default
     */
    public function getQuery($name, $default = null)
    {
        if (array_key_exists($name, $_GET)) {
            return $_GET[$name];
        }
        return $default;
    }
}