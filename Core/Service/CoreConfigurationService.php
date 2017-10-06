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

namespace Scalar\Core\Service;

use Exception;
use Scalar\Config\IniConfig;
use Scalar\Core\Scalar;
use Scalar\IO\File;
use Scalar\Util\ScalarArray;

class CoreConfigurationService extends CoreService
{

    /**
     * Low-level ini configuration
     * @var IniConfig
     */
    private $iniConfig;

    /**
     * Overrides for value-including
     * @var ScalarArray
     */
    private $overrides;

    /**
     * Regular expression to find value-include strings
     * @var string
     */
    private $injectableRegex = '/{{(?<Path>[^}]*)}}/x';

    /**
     * CoreConfigurationService constructor.
     */
    public function __construct()
    {
        parent::__construct("Config", false);
    }

    /**
     * Set a config value at path
     *
     * @param string $path
     * @param mixed $value
     * @return static
     */
    public function set
    (
        $path,
        $value
    )
    {
        $this->iniConfig->setPath($path, $value);
        return $this;
    }

    /**
     * Set default value in config if not present
     *
     * @param $path
     * @param $value
     * @return static
     */
    public function setDefault
    (
        $path,
        $value
    )
    {
        $this->iniConfig->setDefaultPath($path, $value);
        return $this;
    }

    /**
     * Retrieve value stored in config
     *
     * @param $path
     * @param $default
     * @param bool $placeholder
     * @return mixed
     */
    public function get
    (
        $path,
        $default = null,
        $placeholder = true
    )
    {
        $result = $this->iniConfig->getPath($path, $default);
        if (is_string($result) && $placeholder) {
            preg_match_all
            (
                $this->injectableRegex,
                $result,
                $inConfigPlaceholders,
                PREG_SET_ORDER,
                0
            );

            foreach ($inConfigPlaceholders as $injectable) {
                $path = $injectable['Path'];
                if ($this->overrides->containsPath($path)) {
                    $result = str_replace($injectable[0], $this->overrides->getPath($path), $result);
                } else if ($this->has($path)) {
                    $result = str_replace($injectable[0], $this->get($path), $result);
                }
            }
        }
        return $result;
    }

    /**
     * Check if the config contains this key
     *
     * @param $path
     * @return bool
     */
    public function has
    (
        $path
    )
    {
        return $this->iniConfig->hasPath($path);
    }

    /**
     * Add override for in-config variable inclusion
     *
     * @param string $path
     * @param string $value
     */
    public function addOverride
    (
        $path,
        $value
    )
    {
        $this->overrides->setPath($path, $value);
    }

    /**
     * Initialize service for work
     *
     * @return bool
     */
    public function setup()
    {
        $this->overrides = new ScalarArray();
        $this->iniConfig = new IniConfig(new File(SCALAR_CORE . '/config.ini', true));

        $this->iniConfig->load();

        return true;
    }

    /**
     * Destruct service and execute tearDown tasks
     *
     * @return bool
     */
    public function tearDown()
    {
        try {
            $this->iniConfig->save();
        } catch (Exception $ex) {
            $coreLogger = Scalar::getService(Scalar::SERVICE_CORE_LOGGER);
            $coreLogger->e('An error occurred while saving core configuration: ' . $ex);
        }

        return true;
    }
}