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


use Scalar\Core\Scalar;
use Scalar\Plugin\Plugin;
use Scalar\Plugin\PluginManager;

class CorePluginService extends CoreService
{

    /**
     * CoreLogger instance
     * @var CoreLoggerService
     */
    private $coreLogger;

    /**
     * CoreLoader instance
     * @var CoreLoaderService
     */
    private $coreLoader;

    /**
     * PluginManager instance
     * @var PluginManager
     */
    private $pluginManager;

    public function __construct()
    {
        $this->coreLogger = Scalar::getService(Scalar::SERVICE_CORE_LOGGER);
        $this->coreLoader = Scalar::getService(Scalar::SERVICE_CORE_LOADER);
        parent::__construct('Plugin', false);
    }

    /**
     * Initialize service for work
     *
     * @return bool
     */
    public function setup()
    {
        $this->coreLogger->i('Initializing plugin manager...');

        $this->pluginManager = new PluginManager($this->coreLoader->getAutoLoader());

        $this->coreLogger->i('Loading global plugins...');

        foreach (glob(self::getGlobalPluginDirectory() . '/*', GLOB_ONLYDIR) as $pluginLocation) {
            if (file_exists($pluginLocation . '/' . PluginManager::PLUGIN_DESCRIPTION_FILE))
                $this->pluginManager->loadPluginFromLocation($pluginLocation);
        }

        $this->coreLogger->i('Loading app-specific plugins...');

        foreach (glob(self::getAppPluginDirectory() . '/*', GLOB_ONLYDIR) as $pluginLocation) {
            if (file_exists($pluginLocation . '/' . PluginManager::PLUGIN_DESCRIPTION_FILE))
                $this->pluginManager->loadPluginFromLocation($pluginLocation);
        }

        $this->coreLogger->i('Checking for failures...');

        foreach ($this->pluginManager->getPluginFailures()->asArray() as $plugin => $failureReason) {
            $this->coreLogger->e('Error while loading "' . $plugin . '"');
            $this->coreLogger->e($failureReason);
        }

        $this->coreLogger->i('Checking for dependency issues...');

        foreach ($this->pluginManager->getDependencyFailures()->asArray() as $dependency => $pluginLocations) {
            $this->coreLogger->e('The following plugins could not load because they are missing "' . $dependency . '"');
            $this->coreLogger->e('[' . join(',', $pluginLocations) . ']');
        }

        $this->coreLogger->i('Enabling plugins...');

        foreach ($this->pluginManager->getPluginMap()->getServices() as $pluginName) {
            /**
             * @var Plugin $plugin
             */
            $plugin = $this->pluginManager->getPluginMap()->getService($pluginName);
            $this->coreLogger->i('Enabling "' . $plugin->getPluginDescription()->getName() . '"');

            if ($this->pluginManager->enablePlugin($pluginName)) {
                $this->coreLogger->i('Enabled "' . $plugin->getPluginDescription()->getName() . '"');
            } else {
                $this->coreLogger->i('Error while enabling "' . $plugin->getPluginDescription()->getName() . '"');
            }
        }

        $this->coreLogger->i('Plugin manager was successfully initialized');
        return true;
    }

    /**
     * Returns the global plugin folder
     * @return string
     */
    public static function getGlobalPluginDirectory()
    {
        return SCALAR_CORE . '/_plugins/';
    }

    /**
     * Returns the app-specific plugin folder
     * @return string
     */
    public static function getAppPluginDirectory()
    {
        return SCALAR_APP . '/Plugins/';
    }

    /**
     * Destruct service and execute tearDown tasks
     *
     * @return bool
     */
    public function tearDown()
    {
        $this->coreLogger->i('Disabling plugins...');

        foreach ($this->pluginManager->getPluginMap()->getServices() as $pluginName) {
            /**
             * @var Plugin $plugin
             */
            $plugin = $this->pluginManager->getPluginMap()->getService($pluginName);
            $this->coreLogger->i('Disabling "' . $plugin->getPluginDescription()->getName() . '"');
            if ($this->pluginManager->disablePlugin($pluginName)) {
                $this->coreLogger->i('Disabled "' . $plugin->getPluginDescription()->getName() . '"');
            } else {
                $this->coreLogger->i('Error while disabling "' . $plugin->getPluginDescription()->getName() . '"');
            }
        }

        return true;
    }
}