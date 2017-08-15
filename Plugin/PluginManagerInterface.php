<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 06.06.17
 * Time: 15:17
 */

namespace Scaly\Plugin;


interface PluginManagerInterface
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
    );

    /**
     * Load plugin
     *
     * @param $pluginName
     * @return void
     */
    public function loadPlugin
    (
        $pluginName
    );

    /**
     * Enable plugin
     *
     * @param $pluginName
     * @return void
     */
    public function enablePlugin
    (
        $pluginName
    );

    /**
     * Disable plugin
     *
     * @param $pluginName
     * @return void
     */
    public function disablePlugin
    (
        $pluginName
    );

    /**
     * Update plugin
     *
     * @param $pluginName
     * @return bool
     */
    public function updatePlugin
    (
        $pluginName
    );

    /**
     * Uninstall plugin
     *
     * @param $pluginName
     * @return bool
     */
    public function uninstallPlugin
    (
        $pluginName
    );

    /**
     * Install plugin
     *
     * @param $pluginName
     * @return bool
     */
    public function installPlugin
    (
        $pluginName
    );

}