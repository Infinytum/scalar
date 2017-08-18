<?php
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