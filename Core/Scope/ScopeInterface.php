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