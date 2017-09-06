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
 * Date: 05.06.17
 * Time: 21:40
 */

namespace Scalar\Config;


use Scalar\Config\Exception\ParseException;
use Scalar\IO\Exception\IOException;
use Scalar\IO\Stream\Stream;
use Scalar\Util\ScalarArray;

class IniConfig extends Config
{

    const ERR_INVALID_INI = 'Tried to load malformed ini file!';

    /**
     * @var bool
     */
    private $sections;

    /**
     * @var int
     */
    private $iniScannerMode;

    /**
     * IniConfig constructor.
     * @param resource|Stream|string $resource
     * @param ScalarArray|array $configArray
     * @param bool $sections
     * @param int $iniScannerMode
     */
    function __construct
    (
        $resource,
        $configArray = [],
        $sections = true,
        $iniScannerMode = INI_SCANNER_TYPED
    )
    {
        parent::__construct
        (
            $resource,
            $configArray
        );

        $this->sections = $sections;
        $this->iniScannerMode = $iniScannerMode;
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
        $iniArray = parse_ini_string
        (
            $this->resource->getContents(),
            $this->sections,
            $this->iniScannerMode
        );

        if ($iniArray === false) {
            throw new ParseException
            (
                self::ERR_INVALID_INI
            );
        }

        $this->resource->rewind();
        $this->config = new ScalarArray($iniArray);

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
        if ($this->sections) {
            foreach ($this->config->asArray() as $section => $section_data) {
                $this->resource->write("[$section]" . PHP_EOL);
                foreach ($section_data as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $entry) {
                            $this->resource->write($key . "[] = \"$entry\"" . PHP_EOL);
                        }
                    } else {
                        if (is_bool($value))
                            $value = $value ? "true" : "false";
                        $this->resource->write("$key = \"$value\"" . PHP_EOL);
                    }
                }
            }
        } else {
            foreach ($this->config->asArray() as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $entry) {
                        $this->resource->write($key . "[] = \"$entry\"" . PHP_EOL);
                    }
                } else {
                    $this->resource->write("$key = \"$value\"" . PHP_EOL);
                }
            }
        }
    }
}