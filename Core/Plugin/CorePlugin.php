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

namespace Scalar\Core\Plugin;


use Scalar\Config\IniConfig;
use Scalar\Core\Service\CorePluginService;
use Scalar\IO\File;
use Scalar\Plugin\Plugin;

abstract class CorePlugin extends Plugin
{

    /**
     * @var IniConfig $globalConfig
     */
    private $globalConfig = null;

    /**
     * @var IniConfig $appConfig
     */
    private $appConfig = null;

    /**
     * Returns this plugins home folder
     *
     * @return string
     */
    public function getAppPluginFolder()
    {
        return CorePluginService::getAppPluginDirectory() . $this->getPluginDescription()->getName();
    }

    /**
     * Returns this plugins home folder
     *
     * @return string
     */
    public function getGlobalPluginFolder()
    {
        return CorePluginService::getGlobalPluginDirectory() . $this->getPluginDescription()->getName();
    }

    public function getGlobalConfig()
    {
        if (!$this->globalConfig) {
            $this->globalConfig = new IniConfig
            (
                new File
                (
                    CorePluginService::getGlobalPluginDirectory() . $this->getPluginDescription()->getName() . '/config.ini',
                    true
                )
            );
        }
        return $this->globalConfig;
    }

    public function getAppConfig()
    {
        if (!$this->appConfig) {
            $this->appConfig = new IniConfig
            (
                new File
                (
                    CorePluginService::getAppPluginDirectory() . $this->getPluginDescription()->getName() . '/config.ini',
                    true
                )
            );
        }
        return $this->appConfig;
    }

}