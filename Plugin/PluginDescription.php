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

/**
 * Class PluginDescription
 * @package Scalar\Plugin
 */
class PluginDescription
{

    /**
     * Unique plugin identifier
     *
     * @var string
     */
    private $id;

    /**
     * Repository you installed this plugin from
     *
     * @var string
     */
    private $repository;

    /**
     * Plugin name
     *
     * @var string
     */
    private $name;

    /**
     * Custom plugin version string
     *
     * @var string
     */
    private $version;

    /**
     * Plugin namespace
     *
     * @var string
     */
    private $namespace;

    /**
     * Plugin main class
     *
     * @var string
     */
    private $main;

    /**
     * Auto-incremental package version identifier
     *
     * @var int
     */
    private $packageVersion;

    /**
     * Plugin author
     *
     * @var string
     */
    private $author;

    /**
     * Custom plugin description
     *
     * @var string
     */
    private $description;

    /**
     * Plugins this plugin depends on
     *
     * @var array
     */
    private $dependencies;

    /**
     * PluginDescription constructor.
     * @param string $id
     * @param string $repository
     * @param string $name
     * @param string $version
     * @param string $main
     * @param string $namespace
     * @param int $packageVersion
     * @param string $author
     * @param string $description
     * @param array $dependencies
     */
    public function __construct
    (
        $id,
        $repository,
        $name,
        $version,
        $main,
        $namespace,
        $packageVersion,
        $author,
        $description,
        $dependencies = []
    )
    {
        $this->id = $id;
        $this->repository = $repository;
        $this->name = $name;
        $this->version = $version;
        $this->namespace = $namespace;
        $this->main = $main;
        $this->packageVersion = $packageVersion;
        $this->author = $author;
        $this->description = $description;
        $this->dependencies = $dependencies;
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return PluginDescription
     */
    public function withId($id)
    {
        $newInstance = clone $this;
        $newInstance->id = $id;
        return $newInstance;
    }

    /**
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param string $repository
     * @return PluginDescription
     */
    public function withRepository($repository)
    {
        $newInstance = clone $this;
        $newInstance->repository = $repository;
        return $newInstance;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PluginDescription
     */
    public function withName($name)
    {
        $newInstance = clone $this;
        $newInstance->name = $name;
        return $newInstance;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return PluginDescription
     */
    public function withVersion($version)
    {
        $newInstance = clone $this;
        $newInstance->version = $version;
        return $newInstance;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getAbsoluteNamespace()
    {
        return '\\Scalar\\App\\Plugin\\' . $this->namespace . '\\';
    }

    /**
     * @param string $namespace
     * @return PluginDescription
     */
    public function withNamespace($namespace)
    {
        $newInstance = clone $this;
        $newInstance->namespace = $namespace;
        return $newInstance;
    }

    /**
     * @return string
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * @return string
     */
    public function getAbsoluteMain()
    {
        return $this->getAbsoluteNamespace() . $this->main;
    }

    /**
     * @param string $main
     * @return PluginDescription
     */
    public function withMain($main)
    {
        $newInstance = clone $this;
        $newInstance->main = $main;
        return $newInstance;
    }

    /**
     * @return int
     */
    public function getPackageVersion()
    {
        return $this->packageVersion;
    }

    /**
     * @param int $packageVersion
     * @return PluginDescription
     */
    public function withPackageVersion($packageVersion)
    {
        $newInstance = clone $this;
        $newInstance->packageVersion = $packageVersion;
        return $newInstance;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     * @return PluginDescription
     */
    public function withAuthor($author)
    {
        $newInstance = clone $this;
        $newInstance->author = $author;
        return $newInstance;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return PluginDescription
     */
    public function withDescription($description)
    {
        $newInstance = clone $this;
        $newInstance->description = $description;
        return $newInstance;
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param array $dependencies
     * @return PluginDescription
     */
    public function setDependencies($dependencies)
    {
        $newInstance = clone $this;
        $newInstance->dependencies = $dependencies;
        return $newInstance;
    }




}