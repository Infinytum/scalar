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
 * Date: 07.06.17
 * Time: 22:36
 */

namespace Scalar\Plugin\Factory;


use Scalar\Plugin\PluginDescription;

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
            $pluginInfo['main'],
            $pluginInfo['namespace'],
            $pluginInfo['package_version'],
            $pluginInfo['author'],
            $pluginInfo['description'],
            $pluginInfo['depends']
        );
    }

    /**
     * Create instance of plugin description
     *
     * @param string|null $id
     * @param string|null $repository
     * @param string|null $name
     * @param string|null $version
     * @param string $namespace
     * @param string $main
     * @param int|null $packageVersion
     * @param string|null $author
     * @param string|null $description
     * @param array $dependency
     * @return PluginDescription
     */
    public function createPluginDescription
    (
        $id = 'undefined',
        $repository = 'undefined',
        $name = 'undefined',
        $version = 'undefined',
        $namespace = 'undefined',
        $main = 'main',
        $packageVersion = -1,
        $author = 'undefined',
        $description = 'undefined',
        $dependency = []
    )
    {

        return new PluginDescription
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
            $dependency
        );
    }
}