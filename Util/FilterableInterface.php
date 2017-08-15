<?php

/**
 * Interface Filterable
 *
 * Template for any filterable class
 */

namespace Scaly\Util;

interface FilterableInterface
{

    /**
     * Retrieve sub-data from all objects in array
     * @param $lambda callable filter
     * @return self
     */
    public function select($lambda);

    /**
     * Filter data from all objects in array
     * @param $lambda callable filter
     * @return self
     */
    public function where($lambda);

    /**
     * Get first object or default value
     * @param $default mixed
     * @return mixed
     */
    public function firstOrDefault($default = null);

    /**
     * Get last object or default value
     * @param $default mixed
     * @return mixed
     */
    public function lastOrDefault($default = null);

    /**
     * Check if data contains any entries
     * @return bool
     */
    public function any();

    /**
     * Check if all entries match filter
     * @param $filter callable
     * @return bool
     */
    public function all($filter);


    /**
     * Execute callback for each entry
     * @param $callback callable
     * @return self
     */
    public function each($callback);

    /**
     * Get the amount of entries
     * @return int
     */
    public function count();

    /**
     * Check if data contains entry
     * @param $entry mixed
     * @return bool
     */
    public function contains($entry);

    /**
     * Filter unique data
     * @return self
     */
    public function distinct();

    /**
     * Everything except provided entries
     * @param $entryOrArray mixed
     * @return self
     */
    public function except($entryOrArray);

    /**
     * Supply function to sort data
     * @param $comparable callable sort function
     * @return self
     */
    public function orderBy($comparable);

    /**
     * Reverse data
     * @return self
     */
    public function reverse();

    /**
     * Return filtered data as array
     * @return array
     */
    public function asArray();

    /**
     * Return filtered data as dictionary
     * @param $keyValueAssignment callable key value assigner
     * @return array;
     */
    public function asDictionary($keyValueAssignment);
}