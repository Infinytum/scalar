<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 07.06.17
 * Time: 22:36
 */

namespace Scaly\Plugin\Factory;


use Scaly\Plugin\PluginDescription;

class PluginDescriptionFactory implements PluginDescriptionFactoryInterface
{

    /**
     * @param $pluginInfo
     * @return PluginDescription
     */
    public function createPluginDescriptionFromPackage
    (
        $pluginInfo
    )
    {
        return new PluginDescription
        (
            $pluginInfo['_id'],
            $pluginInfo['_repository'],
            $pluginInfo['name'],
            $pluginInfo['version'],
            $pluginInfo['package_version'],
            $pluginInfo['author'],
            $pluginInfo['description']
        );
    }

    /**
     * Create instance of plugin description
     *
     * @param string|null $id
     * @param string|null $repository
     * @param string|null $name
     * @param string|null $version
     * @param int|null $packageVersion
     * @param string|null $author
     * @param string|null $description
     * @return PluginDescription
     */
    public function createPluginDescription
    (
        $id = null,
        $repository = null,
        $name = null,
        $version = null,
        $packageVersion = null,
        $author = null,
        $description = null
    )
    {
        if ($id == null) {
            $id = 'undefined';
        }

        if ($repository == null) {
            $repository = 'undefined';
        }

        if ($name == null) {
            $name = 'undefined';
        }

        if ($version == null) {
            $version = 'undefined';
        }

        if ($packageVersion == null) {
            $packageVersion = -1;
        }

        if ($author == null) {
            $author = 'undefined';
        }

        if ($description == null) {
            $description = 'undefined';
        }

        return new PluginDescription
        (
            $id,
            $repository,
            $name,
            $version,
            $packageVersion,
            $author,
            $description
        );
    }
}