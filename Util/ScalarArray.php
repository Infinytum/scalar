<?php

namespace Scalar\Util;

class ScalarArray implements \ArrayAccess, FilterableInterface
{

    private $array;

    /**
     * ScalarArray constructor.
     * @param $array array
     */
    public function __construct
    (
        $array = []
    )
    {
        $this->array = $array;
    }

    /**
     * Select sub-data from data
     * @param callable $lambda
     * @return $this
     */
    public function select
    (
        $lambda
    )
    {
        $data = array();
        foreach ($this->array as $key => $value) {
            array_push($data, $lambda($key, $value));
        }
        $this->array = $data;
        return $this;
    }

    /**
     * Keep data entries where condition is met
     * @param callable $lambda
     * @return $this
     */
    public function where
    (
        $lambda
    )
    {
        $data = array();
        foreach ($this->array as $key => $value) {
            if ($lambda($key, $value)) {
                array_push($data, $value);
            }
        }
        $this->array = $data;
        return $this;
    }

    /**
     * Order data according to your data comparable
     * @param callable $comparable
     * @return $this
     */
    public function orderBy
    (
        $comparable
    )
    {
        if ($this->isAssoc($this->array)) {
            uasort($this->array, $comparable);
        } else {
            usort($this->array, $comparable);
        }
        return $this;
    }

    /**
     * Check if Array has custom associative keys
     * @param array $arr
     * @return bool
     */
    private function isAssoc
    (
        $arr
    )
    {
        if (array() === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Remove duplicate data entries
     * @return $this
     */
    public function distinct()
    {
        if ($this->isAssoc($this->array)) {
            $this->array = array_unique($this->array);
        } else {
            $this->array = array_values(array_unique($this->array));
        }
        return $this;
    }

    /**
     * Execute action for each entry
     * @param callable $callback
     * @return $this
     */
    public function each
    (
        $callback
    )
    {
        foreach ($this->array as $key => $value) {
            $callback($key, $value);
            unset($this->array[$key]);
            $this->array[$key] = $value;
        }
        return $this;
    }

    /**
     * Get the amount of entries
     * @return int
     */
    public function count()
    {
        return count($this->array);
    }

    /**
     * Exclude entries from data
     * @param mixed $entryOrArray
     * @return $this
     */
    public function except
    (
        $entryOrArray
    )
    {
        if (is_array($entryOrArray)) {
            foreach ($entryOrArray as $key => $val) {
                $this->except($val);
            }
        } else {
            if ($this->contains($entryOrArray)) {
                if ($this->isAssoc($this->array)) {
                    unset($this->array[array_search($entryOrArray, $this->array)]);
                } else {
                    unset($this->array[array_search($entryOrArray, $this->array)]);
                    $this->array = array_values($this->array);
                }
            }
        }
        return $this;
    }

    /**
     * Check if data contains entry
     * @param $entry mixed
     * @return bool
     */
    public function contains
    (
        $entry
    )
    {
        if ($this->isAssoc($this->array)) {
            return array_key_exists($entry, $this->array);
        } else {
            return array_search($entry, $this->array) !== false;
        }
    }

    /**
     * Check if data contains any entries
     * @return bool
     */
    public function any()
    {
        return count($this->array) > 0;
    }

    /**
     * Check if all entries match filter
     * @param $filter callable
     * @return bool
     */
    public function all
    (
        $filter
    )
    {
        foreach ($this->array as $key => $value) {
            if (!$filter($key, $value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Return filtered data as array
     * @return array
     */
    public function asArray()
    {
        return $this->array;
    }

    /**
     * Return filtered data as dictionary
     * @param $keyValueAssignment callable key value assigner
     * @return array;
     */
    public function asDictionary
    (
        $keyValueAssignment
    )
    {
        $dictionary = array();
        foreach ($this->array as $key => $value) {
            $dictionary = $dictionary + $keyValueAssignment($key, $value);
        }
        return $dictionary;
    }

    /**
     * Get first object or default value
     * @param $default mixed
     * @return mixed
     */
    public function firstOrDefault
    (
        $default = null
    )
    {
        if (count($this->array) > 0) {
            return $this->array[0];
        } else {
            return $default;
        }
    }

    /**
     * Get last object or default value
     * @param $default mixed
     * @return mixed
     */
    public function lastOrDefault
    (
        $default = null
    )
    {
        if (count($this->array) > 0) {
            return $this->array[count($this->array) - 1];
        } else {
            return $default;
        }
    }

    /**
     * Reverse data
     * @return self
     */
    public function reverse()
    {
        $this->array = array_reverse($this->array, true);
        return $this;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset
    (
        $offset
    )
    {
        if ($this->offsetExists($offset)) {
            unset($this->array[$offset]);
        }
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists
    (
        $offset
    )
    {
        return $this->contains($offset);
    }

    function __get
    (
        $name
    )
    {
        return $this->offsetGet($name);
    }

    function __set
    (
        $name,
        $value
    )
    {
        $this->offsetSet($name, $value);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet
    (
        $offset
    )
    {
        if ($this->offsetExists($offset)) {
            return $this->array[$offset];
        }
        return null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet
    (
        $offset,
        $value
    )
    {
        $this->array[$offset] = $value;
    }

    /**
     * Put value in array at dot-delimited path
     * @param $path string
     * @param $value mixed
     * @return void
     */
    public function putPath
    (
        $path,
        $value
    )
    {
        $result = $this->getPath($path, array());
        if ($value !== null)
            array_push($result, $value);
        $this->setPath($path, $result);
    }

    /**
     * Get array value by dot-delimited path
     * @param $path string
     * @param $default mixed
     * @return mixed|null
     */
    public function getPath
    (
        $path,
        $default = null
    )
    {
        $temp = &$this->array;
        foreach (explode(".", $path) as $key) {
            if (array_key_exists($key, $temp))
                $temp =& $temp[$key];
            else
                return $default;
        }
        return $temp;
    }

    /**
     * Set array value by dot-delimited path
     * @param $path string
     * @param $value mixed
     * @return void
     */
    public function setPath
    (
        $path,
        $value
    )
    {
        $array = &$this->array;
        $keys = explode(".", $path);
        foreach ($keys as $key) {
            if (!isset($array[$key])) {
                $array[$key] = array();
            }
            $array = &$array[$key];
        }
        $array = $value;
    }

    /**
     * Check if path exists
     * @param $path string
     * @return bool
     */
    public function containsPath
    (
        $path
    )
    {
        $temp = &$this->array;
        foreach (explode(".", $path) as $key) {
            if (array_key_exists($key, $temp))
                $temp =& $temp[$key];
            else
                return false;
        }
        return true;
    }

    public function get
    (
        $key,
        $default = null
    )
    {
        $returnValue = $default;

        if ($this->contains($key)) {
            $returnValue = $this->array[$key];
        }

        return $returnValue;
    }

    public function set
    (
        $key,
        $value
    )
    {
        $this->array[$key] = $value;
    }

    public function delete
    (
        $key
    )
    {
        if ($this->isAssoc($this->array)) {
            array_splice($array, $key, 1);
        } else {
            unset($this->array[$key]);
        }
    }
}