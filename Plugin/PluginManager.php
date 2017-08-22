<?php
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