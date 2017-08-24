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

namespace Scalar\Cache\Storage;

use Scalar\Cache\Exception\CacheStorageException;

/**
 * Interface CacheStorageInterface
 *
 * Persistence Providers for caching
 *
 * @package Scalar\Cache\Storage
 */
interface CacheStorageInterface
{

    /**
     * Check if this storage is available
     *
     * @return bool
     */
    public static function isAvailable();

    /**
     * Store data in persistence layer of cache
     *
     * @param string $key Unique identifier of your data
     * @param mixed $data Data to store
     * @return bool True if storing succeeded
     * @throws CacheStorageException
     */
    public function store($key, $data);

    /**
     * Retrieve data from cache
     *
     * @param string $key Unique identifier of your data
     * @return mixed Stored data from persistence
     * @throws CacheStorageException
     */
    public function retrieve($key);

    /**
     * Delete data from cache
     *
     * @param string $key Unique identifier of your data
     * @return bool True if removal was successful
     * @throws CacheStorageException
     */
    public function delete($key);

    /**
     * Check if data exists
     *
     * @param string $key Unique identifier of your data
     * @return bool True if key was found
     * @throws CacheStorageException
     */
    public function check($key);

    /**
     * Delete all data stored in cache
     *
     * @throws CacheStorageException
     */
    public function clear();

}