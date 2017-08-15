<?php

/**
 * Interface Filterable
 *
 * Template for any filterable class
 */

namespace Scaly\Util;

interface StaticFilterableInterface
{

    /**
     * Retrieve sub-data from all objects in array
     * @param $lambda callable filter
     * @return self
     */
    public static function select($lambda);

    /**
     * Filter data from all objects in array
     * @param $lambda callable filter
     * @return self
     */
    public static function where($lambda);

    /**
     * Get first object or default value
     * @param $default mixed
     * @return mixed
     */
    public static function firstOrDefault($default = null);

    /**
     * Get last object or default value
     * @param $default mixed
     * @return mixed
     */
    public static function lastOrDefault($default = null);

    /**
     * Check if data contains any entries
     * @return bool
     */
    public static function any();

    /**
     * Check if all entries match filter
     * @param $filter callable
     * @return bool
     */
    public static function all($filter);


    /**
     * Execute callback for each entry
     * @param $callback callable
     * @return self
     */
    public static function each($callback);

    /**
     * Get the amount of entries
     * @return int
     */
    public static function count();

    /**
     * Check if data contains entry
     * @param $entry mixed
     * @return bool
     */
    public static function contains($entry);

    /**
     * Filter unique data
     * @return self
     */
    public static function distinct();

    /**
     * Everything except provided entries
     * @param $entryOrArray mixed
     * @return self
     */
    public static function except($entryOrArray);

    /**
     * Supply function to sort data
     * @param $comparable callable sort function
     * @return self
     */
    public static function orderBy($comparable);

    /**
     * Reverse data
     * @return self
     */
    public static function reverse();

    /**
     * Return filtered data as array
     * @return array
     */
    public static function asArray();

    /**
     * Return filtered data as dictionary
     * @param $keyValueAssignment callable key value assigner
     * @return array;
     */
    public static function asDictionary($keyValueAssignment);
}