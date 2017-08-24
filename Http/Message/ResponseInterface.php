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

namespace Scalar\Http\Message;


/**
 * Interface ResponseInterface
 *
 * Abstract server response implementation
 *
 * @package Scalar\Http\Message
 */
interface ResponseInterface extends MessageInterface
{
    /**
     * Get response code
     * @return int Status code.
     */
    public function getStatusCode();

    /**
     * Get instance with status code
     * @param int $code
     * @param string $reason
     * @return static
     */
    public function withStatus($code, $reason = '');

    /**
     * Get response reason
     * @return string Reason
     */
    public function getReasonPhrase();

    /**
     * Get all CustomArguments
     * @return string[][] Returns an associative array
     */
    public function getCustomArguments();

    /**
     * Check if CustomArgument is present
     * @param string $name CustomArgument name.
     * @return bool
     */
    public function hasCustomArgument($name);

    /**
     * Get CustomArgument values as array
     * @param string $name CustomArgument name.
     * @return string[] An array of string values
     */
    public function getCustomArgument($name);

    /**
     * Get instance with CustomArgument
     * @param string $name CustomArgument name.
     * @param string|string[] $value CustomArgument value.
     * @return static
     */
    public function withCustomArgument($name, $value);

    /**
     * Get message with added CustomArgument
     * @param string $name CustomArgument to add.
     * @param string|string[] $value CustomArgument value.
     * @return static
     */
    public function withAddedCustomArgument($name, $value);

    /**
     * Get message without CustomArgument
     * @param string $name CustomArgument to remove.
     * @return static
     */
    public function withoutCustomArgument($name);
}