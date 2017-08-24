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
 * User: nila
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