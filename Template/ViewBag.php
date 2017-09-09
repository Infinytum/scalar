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
 * Date: 7/11/17
 * Time: 1:26 PM
 */

namespace Scalar\Template;


use Scalar\Util\ScalarArray;

class ViewBag
{

    /**
     * @var ScalarArray
     */
    private static $array;

    /**
     * Add value to existing template string
     * This will convert your value to an array
     *
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public static function add
    (
        $key,
        $val
    )
    {
        self::__staticConstructor();

        $array = [];
        if (self::has($key)) {
            $value = self::get($key);
            if (is_array($value)) {
                $array = $value;
            } else {
                $array = [$value];
            }
        }

        $array = array_merge($array, [$val]);
        self::set($key, $array);
    }

    /**
     * Add value to existing template string at given path
     * This will convert your value to an array
     *
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public static function putPath
    (
        $key,
        $val
    )
    {
        self::__staticConstructor();
        self::$array->putPath($key, $val);
    }

    /**
     * Check if given template string already exists
     *
     * @param string $key
     * @return bool
     */
    public static function has
    (
        $key
    )
    {
        self::__staticConstructor();
        return self::$array->contains($key);
    }

    /**
     * Check if given template string path already exists
     *
     * @param string $key
     * @return bool
     */
    public static function hasPath
    (
        $key
    )
    {
        self::__staticConstructor();
        return self::$array->containsPath($key);
    }

    /**
     * Returns current definition of template string
     *
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    public static function get
    (
        $key,
        $default = null
    )
    {
        self::__staticConstructor();
        return self::$array->get($key, $default);
    }

    /**
     * Returns current definition of template string at given path
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getPath
    (
        $key,
        $default = null
    )
    {
        self::__staticConstructor();
        return self::$array->getPath($key, $default);
    }

    /**
     * Set template string to specific value
     *
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public static function set
    (
        $key,
        $val
    )
    {
        self::__staticConstructor();
        self::$array->set($key, $val);
    }

    /**
     * Set template string to specific value at given path
     *
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public static function setPath
    (
        $key,
        $val
    )
    {
        self::__staticConstructor();
        self::$array->setPath($key, $val);
    }

    /**
     * Get ViewBag as array
     *
     * @return array
     */
    public static function getArray()
    {
        self::__staticConstructor();
        return self::$array->asArray();
    }

    private static function __staticConstructor()
    {
        if (self::$array === null) {
            self::$array = new ScalarArray();
        }
    }

}