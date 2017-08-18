<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 7/10/17
 * Time: 9:09 AM
 */

namespace Scalar\Core\Scope;

interface ScopeInterface
{


    /**
     * Get parent scope
     *
     * @return static|null Null if no parent was stored
     */
    public function getParentScope();

    /**
     * Define a parent scope for this instance
     *
     * @param $parentScope static
     * @return void
     */
    public function setParentScope
    (
        $parentScope
    );

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
    );

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
    );

    /**
     * Check if scoped variable exists
     *
     * @param mixed $variableKey Name / Identifier of a scoped variable
     * @return bool
     */
    public function has
    (
        $variableKey
    );


}