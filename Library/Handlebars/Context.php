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
 * Handlebars context
 * Context for a template
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @author    Behrooz Shabani <everplays@gmail.com>
 * @author    Mardix <https://github.com/mardix>
 * @copyright 2012 (c) ParsPooyesh Co
 * @copyright 2013 (c) Behrooz Shabani
 * @copyright 2013 (c) Mardix
 * @license   MIT
 * @link      http://voodoophp.org/docs/handlebars
 */

namespace Handlebars;

use InvalidArgumentException;
use Scalar\Database\Table\DatabaseTable;
use Scalar\Util\ScalarArray;

class Context
{

    /**
     * @var array stack for context only top stack is available
     */
    protected $stack = [];

    /**
     * @var array index stack for sections
     */
    protected $index = [];

    /**
     * @var array key stack for objects
     */
    protected $key = [];

    /**
     * Mustache rendering Context constructor.
     *
     * @param mixed $context Default rendering context (default: null)
     */
    public function __construct($context = null)
    {
        if ($context !== null) {
            $this->stack = [$context];
        }
    }

    /**
     * Push an Index onto the index stack
     *
     * @param integer $index Index of the current section item.
     *
     * @return void
     */
    public function pushIndex($index)
    {
        array_push($this->index, $index);
    }

    /**
     * Push a Key onto the key stack
     *
     * @param string $key Key of the current object property.
     *
     * @return void
     */
    public function pushKey($key)
    {
        array_push($this->key, $key);
    }

    /**
     * Pop the last Context frame from the stack.
     *
     * @return mixed Last Context frame (object or array)
     */
    public function pop()
    {
        return array_pop($this->stack);
    }

    /**
     * Pop the last index from the stack.
     *
     * @return int Last index
     */
    public function popIndex()
    {
        return array_pop($this->index);
    }

    /**
     * Pop the last key from the stack.
     *
     * @return string Last key
     */
    public function popKey()
    {
        return array_pop($this->key);
    }

    /**
     * Get the last Context frame.
     *
     * @return mixed Last Context frame (object or array)
     */
    public function last()
    {
        return end($this->stack);
    }

    /**
     * Get the index of current section item.
     *
     * @return mixed Last index
     */
    public function lastIndex()
    {
        return end($this->index);
    }

    /**
     * Get the key of current object property.
     *
     * @return mixed Last key
     */
    public function lastKey()
    {
        return end($this->key);
    }

    /**
     * Change the current context to one of current context members
     *
     * @param string $variableName name of variable or a callable on current context
     *
     * @return mixed actual value
     */
    public function with($variableName)
    {
        $value = $this->get($variableName);
        $this->push($value);

        return $value;
    }

    /**
     * Get a avariable from current context
     * Supported types :
     * variable , ../variable , variable.variable , .
     *
     * @param string $variableName variavle name to get from current context
     * @param boolean $strict strict search? if not found then throw exception
     *
     * @throws InvalidArgumentException in strict mode and variable not found
     * @return mixed
     */
    public function get($variableName, $strict = false)
    {
        //Need to clean up
        $variableName = trim($variableName);
        $level = 0;
        while (substr($variableName, 0, 3) == '../') {
            $variableName = trim(substr($variableName, 3));
            $level++;
        }
        if (count($this->stack) < $level) {
            if ($strict) {
                throw new InvalidArgumentException(
                    'can not find variable in context'
                );
            }

            return '';
        }
        end($this->stack);
        while ($level) {
            prev($this->stack);
            $level--;
        }
        $current = current($this->stack);
        if (!$variableName) {
            if ($strict) {
                throw new InvalidArgumentException(
                    'can not find variable in context'
                );
            }
            return '';
        } elseif ($variableName == '.' || $variableName == 'this') {
            return $current;
        } else {
            $chunks = explode('.', $variableName);
            foreach ($chunks as $chunk) {
                if (is_string($current) and $current == '') {
                    return $current;
                }
                $current = $this->findVariableInContext($current, $chunk, $strict);
            }
        }
        return $current;
    }

    /**
     * Check if $variable->$inside is available
     *
     * @param mixed $variable variable to check
     * @param string $inside property/method to check
     * @param boolean $strict strict search? if not found then throw exception
     *
     * @throws \InvalidArgumentException in strict mode and variable not found
     * @return boolean true if exist
     */
    private function findVariableInContext($variable, $inside, $strict = false)
    {
        $value = '';
        if (($inside !== '0' && empty($inside)) || ($inside == 'this')) {
            return $variable;
        } elseif (is_array($variable)) {
            if (isset($variable[$inside])) {
                $value = $variable[$inside];
            }
        } elseif (is_object($variable)) {
            if ($variable instanceof DatabaseTable) {
                $value = $this->handleTable($variable, $inside);
            } elseif (is_callable(array($variable, $inside))) {
                $value = call_user_func(array($variable, $inside));
            } elseif (isset($variable->$inside)) {
                $value = $variable->$inside;
            }
        } elseif ($inside === '.') {
            $value = $variable;
        } elseif ($strict) {
            throw new InvalidArgumentException('can not find variable in context');
        }
        return $value;
    }

    /**
     * Process partial section
     *
     * @param Context $context current context
     *
     * @param $name
     * @return string the result
     */
    private function handleTable
    (
        $context,
        $name
    )
    {
        $value = $context;
        if ($value instanceof DatabaseTable) {
            return $value->offsetGet($name);
        } else if (is_array($value) && count($name) > 0) {
            $arr = new ScalarArray($value);
            return $arr->getPath(join('.', $name), "");
        } else if ($value instanceof ScalarArray && count($name) > 0) {
            return $value->getPath(join('.', $name), "");
        } else if ($value instanceof ScalarArray) {
            return $value->asArray();
        } else {
            return $value;
        }
    }

    /**
     * Push a new Context frame onto the stack.
     *
     * @param mixed $value Object or array to use for context
     *
     * @return void
     */
    public function push($value)
    {
        array_push($this->stack, $value);
    }
}
