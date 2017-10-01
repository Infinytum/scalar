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

namespace Scalar\Config;


use Scalar\IO\Factory\StreamFactory;
use Scalar\IO\File;
use Scalar\IO\Stream\Stream;
use Scalar\IO\Stream\StreamInterface;
use Scalar\Util\ScalarArray;

/**
 * Class Config
 *
 * Abstract class which handles basic configuration initialization
 * and basic features
 *
 * @package Scalar\Config
 */
abstract class Config implements ConfigInterface
{

    const ERR_INVALID_RESOURCE = 'Invalid resource passed to Config. Resource must be a stream or file.';
    const ERR_INVALID_CONFIG = 'Invalid configuration passed to Config. Config must be an array or ScalarArray.';

    /**
     * Stream to configuration file
     *
     * @var Stream
     */
    protected $resource;

    /**
     * In-memory configuration array
     *
     * @var ScalarArray
     */
    protected $config;

    /**
     * Config constructor.
     *
     * @param resource|StreamInterface|File $resource Can be a resource or stream interface
     * pointing at the configuration file or a file path. Config will be created if file does
     * not exist.
     * @param array|ScalarArray $config Can be an array or an instance of ScalarArray containing
     * a configuration to use in this instance.
     */
    public function __construct
    (
        $resource,
        $config = []
    )
    {
        $this->setConfigArray($config);

        if ($resource instanceof StreamInterface) {
            $this->resource = $resource;
        } else if ($resource instanceof File) {
            if (!$resource->exists()) {
                $resource->create(0777, true);
                $this->resource = $resource->toStream('c+');
                $this->save();
            } else {
                $this->resource = $resource->toStream('c+');
            }
        } else if (is_resource($resource)) {
            $streamFactory = new StreamFactory();
            $this->resource = $streamFactory->createStreamFromResource($resource);
        } else {
            throw new \InvalidArgumentException
            (
                self::ERR_INVALID_RESOURCE
            );
        }
    }

    public function setConfigArray
    (
        $config
    )
    {
        if ($config instanceof ScalarArray) {
            $this->config = $config;
        } else if (is_array($config)) {
            $this->config = new ScalarArray($config);
        } else {
            throw new \InvalidArgumentException
            (
                self::ERR_INVALID_CONFIG
            );
        }
    }

    /**
     * Set a config value
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function set
    (
        $key,
        $value
    )
    {
        $this->config->set($key, $value);
        return $this;
    }

    /**
     * Set a config value at path
     *
     * @param string $path
     * @param mixed $value
     * @return static
     */
    public function setPath
    (
        $path,
        $value
    )
    {
        $this->config->setPath($path, $value);
        return $this;
    }

    /**
     * Set default value in config if not present
     *
     * @param $key
     * @param $value
     * @return static
     */
    public function setDefault
    (
        $key,
        $value
    )
    {
        if ($this->has($key)) {
            return $this;
        }
        $this->config->set($key, $value);
        $this->save();
        return $this;
    }

    /**
     * Check if the config contains this key
     *
     * @param $key
     * @return bool
     */
    public function has
    (
        $key
    )
    {
        return $this->config->contains($key);
    }

    /**
     * Set default value in config if not present
     *
     * @param $path
     * @param $value
     * @return static
     */
    public function setDefaultPath
    (
        $path,
        $value
    )
    {
        if ($this->hasPath($path)) {
            return $this;
        }
        $this->config->setPath($path, $value);
        $this->save();
        return $this;
    }

    /**
     * Check if the config contains this key
     *
     * @param $path
     * @return bool
     */
    public function hasPath
    (
        $path
    )
    {
        return $this->config->containsPath($path);
    }

    /**
     * Retrieve value stored in config
     *
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get
    (
        $key,
        $default = null
    )
    {
        return $this->config->get($key, $default);
    }

    /**
     * Retrieve value stored in config
     *
     * @param $path
     * @param $default
     * @return mixed
     */
    public function getPath
    (
        $path,
        $default = null
    )
    {
        return $this->config->getPath($path, $default);
    }

    /**
     * Get config map as Scalar Array
     *
     * @return ScalarArray
     */
    public function asScalarArray()
    {
        return clone $this->config;
    }
}