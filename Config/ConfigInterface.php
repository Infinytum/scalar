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


use Scalar\Config\Exception\ParseException;
use Scalar\IO\Exception\IOException;
use Scalar\Util\ScalarArray;

interface ConfigInterface
{

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
    );

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
    );

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
    );

    /**
     * Set default value in config if not present
     *
     * @param $key
     * @param $value
     * @return static
     */
    public function setDefaultPath
    (
        $key,
        $value
    );

    /**
     * Check if the config contains this key
     *
     * @param $key
     * @return bool
     */
    public function has
    (
        $key
    );

    /**
     * Check if the config contains this key
     *
     * @param $path
     * @return bool
     */
    public function hasPath
    (
        $path
    );

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
        $default
    );

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
        $default
    );

    /**
     * Load configuration
     *
     * @return static
     * @throws IOException Will be thrown if data could not be read from disk
     * @throws ParseException Will be thrown if configuration could not be parsed
     */
    public function load();

    /**
     * Save configuration
     *
     * @return static
     * @throws IOException Will be thrown if writing data to disk fails
     */
    public function save();

    /**
     * Get config map as Scalar Array
     *
     * @return ScalarArray
     */
    public function asScalarArray();


    public function setConfigArray
    (
        $config
    );
}