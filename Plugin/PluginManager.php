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


use Scalar\Config\JsonConfig;
use Scalar\Core\ClassLoader\AutoLoader;
use Scalar\Core\Log\CoreLogger;
use Scalar\Core\Scalar;
use Scalar\Core\Service\ServiceMap;
use Scalar\IO\File;
use Scalar\Plugin\Factory\PluginDescriptionFactory;
use Scalar\Util\ScalarArray;

class PluginManager implements PluginManagerInterface
{

    const PLUGIN_DESCRIPTION_FILE = 'plugin.json';

    const ERROR_MISSING_DESCRIBER = 'Plugin Describer \'plugin.json\' was not found';
    const ERROR_ALREADY_REGISTERED = 'Plugin tried to register twice! Check if you have duplicates.';
    const ERROR_MISSING_DEPENDENCY = 'Plugin could not be loaded because of missing dependencies';
    const ERROR_PLUGIN_LOAD_FAILURE = 'Plugin returned an internal load failure.';

    private $pluginMap;

    private $waitMap;

    private $failMap;

    /**
     * @var CoreLogger $logger
     */
    private $logger;

    /**
     * @var AutoLoader $autoLoader
     */
    private $autoLoader;

    public function __construct()
    {
        $this->pluginMap = new ServiceMap();
        $this->waitMap = new ScalarArray();
        $this->failMap = new ScalarArray();

        $this->logger = Scalar::getService(Scalar::SERVICE_CORE_LOGGER);
        $this->autoLoader = Scalar::getService(Scalar::SERVICE_AUTO_LOADER);
    }

    public static function getAppPluginDirectory()
    {
        return SCALAR_APP . '/plugins/';
    }

    public static function getGlobalPluginDirectory()
    {
        return SCALAR_APP . '/plugins/';
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

        $this->logger->v('Loading plugin at "' . $pluginLocation . '"');

        $descriptorFile = $pluginLocation . '/' . self::PLUGIN_DESCRIPTION_FILE;
        if (!file_exists($descriptorFile)) {
            $this->failMap->set($pluginLocation, self::ERROR_MISSING_DESCRIBER);
            return false;
        }

        $jsonConfig = new JsonConfig(new File($descriptorFile, true));
        $jsonConfig->load();

        $pluginDescriptionFactory = new PluginDescriptionFactory();
        $pluginDescription = $pluginDescriptionFactory->createPluginDescriptionFromPackage($jsonConfig->asScalarArray()->asArray());

        $this->logger->i('Loading plugin ' . $pluginDescription->getName() . ' version ' . $pluginDescription->getVersion());

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
            $this->logger->i('Plugin needs ' . $dependency . ' to work correctly. Waiting for dependency to load');
            return false;
        }

        $this->autoLoader->addClassPath
        (
            $pluginDescription->getAbsoluteNamespace(),
            $pluginLocation
        );

        $reflectionClass = new \ReflectionClass($pluginDescription->getAbsoluteMain());

        /**
         * @var Plugin $pluginInstance
         */
        $pluginInstance = $reflectionClass->newInstanceArgs([$pluginLocation, $pluginDescription]);

        if ($pluginInstance->onLoad()) {
            $this->pluginMap->registerServiceValue($pluginDescription->getId(), $pluginInstance);

            if ($this->waitMap->containsPath($pluginDescription->getId())) {
                $waitMap = $this->waitMap->getPath($pluginDescription->getId());
                unset($this->waitMap[$pluginDescription->getId()]);
                foreach ($waitMap as $pluginLocation) {
                    $this->logger->i('Loading dependency-queued plugin...');
                    if ($this->loadPluginFromLocation($pluginLocation)) {
                        unset($this->failMap[$pluginLocation]);
                    }
                }
            }
            $this->logger->i('Plugin ' . $pluginDescription->getName() . ' successfully loaded.');

            return true;
        }

        $this->failMap->set($pluginDescription->getName(), self::ERROR_PLUGIN_LOAD_FAILURE);
        return false;
    }

    /**
     * Load plugin
     *
     * @param $pluginName
     * @param bool $globalPlugin
     * @return bool
     */
    public function loadPlugin
    (
        $pluginName,
        $globalPlugin = false
    )
    {
        return $this->loadPluginFromLocation($globalPlugin ? self::getGlobalPluginDirectory() : self::getAppPluginDirectory() . '/' . $pluginName);
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

        $this->logger->i('Loading plugin ' . $pluginName);
        $plugin = $this->pluginMap->getService($pluginName);

        if ($plugin->onEnable()) {
            $this->logger->i('Successfully enabled plugin ' . $pluginName);
            return true;
        }

        $this->logger->e('Error while enabling ' . $pluginName);
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

        $this->logger->i('Disabling plugin ' . $pluginName);
        $plugin = $this->pluginMap->getService($pluginName);

        if ($plugin->onDisable()) {
            $this->logger->i('Successfully disabled plugin ' . $pluginName);
            return true;
        }

        $this->logger->e('Error while disabling ' . $pluginName);
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

    public function loadPluginDirectory()
    {
        $directories = array_merge(
            glob(self::getGlobalPluginDirectory() . '/*', GLOB_ONLYDIR),
            glob(self::getAppPluginDirectory() . '/*', GLOB_ONLYDIR)
        );

        foreach ($directories as $pluginLocation) {
            $this->loadPluginFromLocation($pluginLocation);
        }

        foreach ($this->failMap->asArray() as $plugin => $failureReason) {
            $this->logger->e($plugin . ':' . $failureReason);
        }

        foreach ($this->waitMap->asArray() as $dependency => $pluginLocations) {
            $this->logger->e('Could not fulfill dependency "' . $dependency . '" for [' . join(',', $pluginLocations) . ']');
        }

        foreach ($this->pluginMap->getServices() as $pluginName) {
            $this->enablePlugin($pluginName);
        }

    }
}