<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 7/10/17
 * Time: 9:23 AM
 */

namespace Scaly\Core\Scope;


use Scaly\Util\ScalyArray;

class Scope implements ScopeInterface, \Serializable
{

    /**
     * @var static
     */
    private $parentScope;

    /**
     * @var ScalyArray
     */
    private $variables;

    public function __construct
    (
        $parentScope = null,
        $variables = null
    )
    {
        $this->parentScope = $parentScope;

        if (is_array($variables)) {
            $this->variables = new ScalyArray($variables);
        } else if ($variables instanceof ScalyArray) {
            $this->variables = $variables;
        }

        $this->variables = new ScalyArray([]);
    }

    /**
     * Get parent scope
     *
     * @return static|null Null if no parent was stored
     */
    public function getParentScope()
    {
        return $this->parentScope;
    }

    /**
     * Define a parent scope for this instance
     *
     * @param $parentScope static
     * @return void
     */
    public function setParentScope
    (
        $parentScope
    )
    {
        $this->parentScope = $parentScope;
    }

    /**
     * Check if scoped variable exists
     *
     * @param mixed $variableKey Name / Identifier of a scoped variable
     * @return bool
     */
    public function has
    (
        $variableKey
    )
    {
        return $this->variables->containsPath($variableKey);
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return http_build_query($this->variables->asArray());
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        parse_str($serialized, $decodedScope);
        $this->variables = new $decodedScope;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Get scoped variable value
     *
     * @param mixed $variableKey Name / Identifier of a scoped variable
     * @param null $defaultValue Default return value if no variable was found
     * @return mixed|$defaultValue
     */
    public function get
    (
        $variableKey,
        $defaultValue = null
    )
    {
        return $this->variables->getPath($variableKey, $defaultValue);
    }

    /**
     * Set scoped variable value
     *
     * @param mixed $variableKey Name / Identifier of a scoped variable
     * @param mixed $variableValue Value to store
     * @return void
     */
    public function set
    (
        $variableKey,
        $variableValue
    )
    {
        $this->variables->setPath($variableKey, $variableValue);
    }


}