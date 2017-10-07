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
 * A flat-file filesystem cache.
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    Alex Soncodi <alex@brokerloop.com>
 * @author    Behrooz Shabani <everplays@gmail.com>
 * @author    Mardix <https://github.com/mardix>
 * @copyright 2013 (c) Brokerloop, Inc.
 * @copyright 2013 (c) Behrooz Shabani
 * @copyright 2013 (c) Mardix
 * @license   MIT
 * @link      http://voodoophp.org/docs/handlebars
 */

namespace Handlebars\Cache;

use Handlebars\Cache;
use InvalidArgumentException;
use RuntimeException;

class Disk implements Cache
{

    private $path = '';
    private $prefix = '';
    private $suffix = '';

    /**
     * Construct the disk cache.
     *
     * @param string $path Filesystem path to the disk cache location
     * @param string $prefix optional file prefix, defaults to empty string
     * @param string $suffix optional file extension, defaults to empty string
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct($path, $prefix = '', $suffix = '')
    {
        if (empty($path)) {
            throw new InvalidArgumentException('Must specify disk cache path');
        } elseif (!is_dir($path)) {
            @mkdir($path, 0777, true);

            if (!is_dir($path)) {
                throw new RuntimeException('Could not create cache file path');
            }
        }

        $this->path = $path;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    /**
     * Get cache for $name if it exists.
     *
     * @param string $name Cache id
     *
     * @return mixed data on hit, boolean false on cache not found
     */
    public function get($name)
    {
        $path = $this->getPath($name);

        return (file_exists($path)) ?
            unserialize(file_get_contents($path)) : false;
    }

    /**
     * Gets the full disk path for a given cache item's file,
     * taking into account the cache path, optional prefix,
     * and optional extension.
     *
     * @param string $name Name of the cache item
     *
     * @return string full disk path of cached item
     */
    private function getPath($name)
    {
        return $this->path . DIRECTORY_SEPARATOR .
            $this->prefix . $name . $this->suffix;
    }

    /**
     * Set a cache
     *
     * @param string $name cache id
     * @param mixed $value data to store
     *
     * @return void
     */
    public function set($name, $value)
    {
        $path = $this->getPath($name);

        file_put_contents($path, serialize($value));
    }

    /**
     * Remove cache
     *
     * @param string $name Cache id
     *
     * @return void
     */
    public function remove($name)
    {
        $path = $this->getPath($name);

        unlink($path);
    }

}
