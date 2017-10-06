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


use Scalar\ClassLoader\AutoLoader;
use Scalar\Config\JsonConfig;
use Scalar\IO\File;
use Scalar\Plugin\Factory\PluginDescriptionFactory;
use Scalar\Service\ServiceMap;
use Scalar\Util\ScalarArray;

class PluginManager
{

    const PLUGIN_DESCRIPTION_FILE = 'plugin.json';

    const ERROR_MISSING_DESCRIBER = 'Plugin Describer \'plugin.json\' was not found';
    const ERROR_ALREADY_REGISTERED = 'Plugin tried to register twice! Check if you have duplicates.';
    const ERROR_MISSING_DEPENDENCY = 'Plugin could not be loaded because of missing dependencies';
    const ERROR_PLUGIN_LOAD_FAILURE = 'Plugin returned an internal load failure.';

    private $pluginMap;

    /**
     * Plugins which could not load because they are missing dependencies
     * @var ScalarArray
     */
    private $waitMap;

    /**
     * Plugins which could not load because they produced errors
     * @var ScalarArray
     */
    private $failMap;

    /**
     * @var AutoLoader
     */
    private $autoLoader;

    public function __construct
    (
        $autoLoader
    )
    {
        $this->autoLoader = $autoLoader;
        $this->pluginMap = new ServiceMap();
        $this->waitMap = new ScalarArray();
        $this->failMap = new ScalarArray();
    }

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
        $pluginLocation = realpath($pluginLocation);

        $descriptorFile = $pluginLocation . '/' . self::PLUGIN_DESCRIPTION_FILE;
        if (!file_exists($descriptorFile)) {
            $this->failMap->set($pluginLocation, self::ERROR_MISSING_DESCRIBER);
            return false;
        }

        $jsonConfig = new JsonConfig(new File($descriptorFile, true, 'r'));
        $jsonConfig->load();

        $pluginDescriptionFactory = new PluginDescriptionFactory();
        $pluginDescription = $pluginDescriptionFactory->createPluginDescriptionFromPackage($jsonConfig->asScalarArray()->asArray());

        if ($this->pluginMap->hasService($pluginDescription->getId())) {
            $this->failMap->set($pluginDescription->getId(), self::ERROR_ALREADY_REGISTERED);
            return false;
        }

        foreach ($pluginDescription->getDependencies() as $dependency) {
            if ($this->pluginMap->hasService($dependency)) {
                continue;
            }
            $this->waitMap->putPath($dependency, $pluginLocation);
            $this->failMap->set($pluginLocation, self::ERROR_MISSING_DEPENDENCY);
            return false;
        }

        $this->autoLoader->addClassPath
        (
            $pluginDescription->getAbsoluteNamespace(),
            $pluginLocation
        );

        $reflectionClass = new \ReflectionClass($pluginDescription->getAbsoluteMain());

        $pluginInstance = $reflectionClass->newInstanceArgs([$pluginLocation, $pluginDescription]);

        if ($pluginInstance->onLoad()) {
            $this->pluginMap->registerServiceValue($pluginDescription->getId(), $pluginInstance);

            if ($this->waitMap->containsPath($pluginDescription->getId())) {
                $waitMap = $this->waitMap->getPath($pluginDescription->getId());
                unset($this->waitMap[$pluginDescription->getId()]);
                foreach ($waitMap as $pluginLocation) {
                    if ($this->loadPluginFromLocation($pluginLocation)) {
                        unset($this->failMap[$pluginLocation]);
                    }
                }
            }

            return true;
        }

        $this->failMap->set($pluginDescription->getName(), self::ERROR_PLUGIN_LOAD_FAILURE);
        return false;
    }

    /**
     * Enable plugin
     *
     * @param $pluginName
     * @return bool
     */
    public function enablePlugin
    (
        $pluginName
    )
    {
        if (!$this->pluginMap->hasService($pluginName)) {
            return false;
        }

        $plugin = $this->pluginMap->getService($pluginName);

        if ($plugin->onEnable()) {
            return true;
        }

        return false;
    }

    /**
     * Disable plugin
     *
     * @param $pluginName
     * @return bool
     */
    public function disablePlugin
    (
        $pluginName
    )
    {
        if (!$this->pluginMap->hasService($pluginName)) {
            return false;
        }
        $plugin = $this->pluginMap->getService($pluginName);

        if ($plugin->onDisable()) {
            return true;
        }
        return false;
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

    public function getPluginMap()
    {
        return clone $this->pluginMap;
    }

    public function getPluginFailures()
    {
        return $this->failMap;
    }

    public function getDependencyFailures()
    {
        return $this->waitMap;
    }
}