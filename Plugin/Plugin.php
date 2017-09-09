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


namespace Scalar\Plugin;


use Scalar\Config\IniConfig;
use Scalar\IO\File;

abstract class Plugin
{

    /**
     * @var PluginDescription $pluginDescription
     */
    private $pluginDescription;

    /**
     * @var string $pluginLocation
     */
    private $pluginLocation;

    /**
     * @var IniConfig $globalConfig
     */
    private $globalConfig = null;

    /**
     * @var IniConfig $appConfig
     */
    private $appConfig = null;

    public function __construct
    (
        $pluginLocation,
        $pluginDescription
    )
    {
        $this->pluginLocation = $pluginLocation;
        $this->pluginDescription = $pluginDescription;
    }

    public function getPluginDescription()
    {
        return $this->pluginDescription;
    }
    /**
     * Returns this plugins home folder
     *
     * @return string
     */
    public function getAppPluginFolder()
    {
        return PluginManager::getAppPluginDirectory() . $this->pluginDescription->getName();
    }

    /**
     * Returns this plugins home folder
     *
     * @return string
     */
    public function getGlobalPluginFolder()
    {
        return PluginManager::getGlobalPluginDirectory() . $this->pluginDescription->getName();
    }

    public function getGlobalConfig()
    {
        if (!$this->globalConfig) {
            $this->globalConfig = new IniConfig
            (
                new File
                (
                    PluginManager::getGlobalPluginDirectory() . $this->pluginDescription->getName() . '/config.ini',
                    true
                )
            );
        }
        return $this->globalConfig;
    }

    public function getAppConfig()
    {
        if (!$this->globalConfig) {
            $this->globalConfig = new IniConfig
            (
                new File
                (
                    PluginManager::getAppPluginDirectory() . $this->pluginDescription->getName() . '/config.ini',
                    true
                )
            );
        }
        return $this->globalConfig;
    }

    /**
     * Called when Scalar loads the plugin from the plugin folder
     *
     * @return bool True if the plugin loaded successfully
     */
    public abstract function onLoad();

    /**
     * Called when Scalar enables the plugin after loading all plugins
     *
     * @return bool True if the plugin enabled successfully
     */
    public abstract function onEnable();

    /**
     * Called when Scalar disables the plugin after the response was sent to the client
     *
     * @return bool True if the plugin disabled successfully
     */
    public abstract function onDisable();

}