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

namespace Scalar\Core\Config;


use Scalar\Config\IniConfig;
use Scalar\Core\Scalar;
use Scalar\Util\ScalarArray;

class ScalarConfig extends IniConfig
{

    private static $instance;

    private $overrides;

    private $injectableRegex = '/{{(?<Path>[^}]*)}}/x';

    function __construct()
    {
        parent::__construct(SCALAR_CORE . '/config.ini', []);
        $this->load();
        self::$instance = $this;
        $this->overrides = new ScalarArray([]);
    }

    /**
     * Get singleton
     *
     * @deprecated
     * @return ScalarConfig
     */
    public static function getInstance()
    {
        return Scalar::getService
        (
            Scalar::SERVICE_SCALAR_CONFIG
        );
    }

    public function get
    (
        $key,
        $default = null,
        $placeholder = true
    )
    {
        $result = parent::get($key, $default);
        if (is_string($result) && $placeholder) {
            preg_match_all
            (
                $this->injectableRegex,
                $result,
                $injectables,
                PREG_SET_ORDER,
                0
            );


            foreach ($injectables as $injectable) {
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

    public function addOverride($path, $value)
    {
        $this->overrides->setPath($path, $value);
    }
}