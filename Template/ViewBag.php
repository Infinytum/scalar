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
 * User: teryx
 * Date: 7/11/17
 * Time: 1:26 PM
 */

namespace Scalar\Template;


class ViewBag
{

    private static $array = [];

    /**
     * Add value to existing template string
     * This will convert your value to an array
     *
     * @param $key
     * @param $val
     */
    public static function add
    (
        $key,
        $val
    )
    {
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

    public static function has
    (
        $key
    )
    {
        return array_key_exists($key, self::$array);
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
        if (self::has($key)) {
            return self::$array[$key];
        }

        return $default;
    }

    /**
     * Set template string to specific value
     *
     * @param $key
     * @param $val
     */
    public static function set
    (
        $key,
        $val
    )
    {
        self::$array[$key] = $val;
    }

    public static function getArray()
    {
        return self::$array;
    }

}