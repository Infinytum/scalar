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

namespace Scalar\Config;

use Scalar\Config\Exception\ParseException;
use Scalar\IO\Exception\IOException;
use Scalar\IO\File;
use Scalar\IO\Stream\Stream;
use Scalar\Util\ScalarArray;

class JsonConfig extends Config
{



    /**
     * JsonConfig constructor.
     * @param resource|Stream|File $resource
     * @param ScalarArray|array $configArray
     */
    public function __construct
    (
        $resource,
        $configArray = []
    )
    {
        parent::__construct
        (
            $resource,
            $configArray
        );
    }

    /**
     * Load configuration
     *
     * @return static
     * @throws IOException Will be thrown if data could not be read from disk
     * @throws ParseException Will be thrown if configuration could not be parsed
     */
    public function load()
    {
        $this->resource->rewind();
        $array = json_decode
        (
            $this->resource->getContents(),
            true
        );

        if (!$array) {
            $array = [];
        }

        $this->config = new ScalarArray($array);
        return $this;
    }

    /**
     * Save configuration
     *
     * @return static
     * @throws IOException Will be thrown if writing data to disk fails
     */
    public function save()
    {
        $this->resource->wipe();
        $this->resource->write
        (
            json_encode
            (
                $this->config->asArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
        return $this;
    }
}