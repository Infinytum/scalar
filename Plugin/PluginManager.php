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
 * Date: 06.06.17
 * Time: 15:17
 */

namespace Scalar\Plugin;


class PluginManager implements PluginManagerInterface
{

    /**
     * Load a plugin's metadata from location
     *
     * @param $pluginLocation
     * @return bool
     */
    public function loadPluginFromLocation
    (
        $pluginLocation
    )
    {
        // TODO: Implement loadPluginFromLocation() method.
    }

    /**
     * Load plugin
     *
     * @param $pluginName
     * @return void
     */
    public function loadPlugin
    (
        $pluginName
    )
    {
        // TODO: Implement loadPlugin() method.
    }

    /**
     * Enable plugin
     *
     * @param $pluginName
     * @return void
     */
    public function enablePlugin
    (
        $pluginName
    )
    {
        // TODO: Implement enablePlugin() method.
    }

    /**
     * Disable plugin
     *
     * @param $pluginName
     * @return void
     */
    public function disablePlugin
    (
        $pluginName
    )
    {
        // TODO: Implement disablePlugin() method.
    }

    /**
     * Update plugin
     *
     * @param $pluginName
     * @return bool
     */
    public function updatePlugin
    (
        $pluginName
    )
    {
        // TODO: Implement updatePlugin() method.
    }

    /**
     * Uninstall plugin
     *
     * @param $pluginName
     * @return bool
     */
    public function uninstallPlugin
    (
        $pluginName
    )
    {
        // TODO: Implement uninstallPlugin() method.
    }

    /**
     * Install plugin
     *
     * @param $pluginName
     * @return bool
     */
    public function installPlugin
    (
        $pluginName
    )
    {
        // TODO: Implement installPlugin() method.
    }
}