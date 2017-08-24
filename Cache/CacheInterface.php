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

namespace Scalar\Cache;


/**
 * Interface CacheInterface
 *
 * Abstract Cache Provider
 *
 * @package Scalar\Cache
 */
interface CacheInterface
{


    /**
     * Retrieve data from cache layer
     * @param string $key Unique identifier of your data
     * @param null $default What to return if requested data is not in cache
     * @return mixed Return cached data or default
     * @throws \Scalar\Cache\Exception\InvalidArgumentException
     */
    public function get($key, $default = null);

    /**
     * Check if data is present on cache layer
     * @param string $key Unique identifier of your data
     * @return bool Returns if your data is present or not
     * @throws \Scalar\Cache\Exception\InvalidArgumentException
     */
    public function has($key);

    /**
     * Remove data from cache layer
     * @param string $key Unique identifier of your data
     * @return bool True if removal was successful else false
     * @throws \Scalar\Cache\Exception\InvalidArgumentException
     */
    public function delete($key);

    /**
     * Store data in cache layer
     * @param string $key Unique identifier of your data
     * @param mixed $data to store in cache layer
     * @return bool True if storing was successful else false
     * @throws \Scalar\Cache\Exception\InvalidArgumentException
     */
    public function set($key, $data);

}