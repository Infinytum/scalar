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
 * Date: 06.06.17
 * Time: 15:54
 */

namespace Scalar\Repository;


use Scalar\IO\UriInterface;
use Scalar\Plugin\PluginDescription;

interface RepositoryInterface
{

    /**
     * Get remote repository name
     *
     * @return string
     */
    public function getName();

    /**
     * Set remote repository name
     *
     * @param string $name
     * @return static
     */
    public function withName
    (
        $name
    );

    /**
     * Get remote repository URI
     *
     * @return UriInterface
     */
    public function getUri();

    /**
     * Get repository instance with URI
     *
     * @param UriInterface $uri New URI
     * @return static
     */
    public function withUri
    (
        $uri
    );

    /**
     * Get API token for remote repository
     *
     * @return string
     */
    public function getApiToken();

    /**
     * Get repository instance with api token
     *
     * @param string $apiToken New api token
     * @return static
     */
    public function withApiToken
    (
        $apiToken
    );

    /**
     * Search for Plugins by name
     *
     * @param $pluginName
     * @return array
     */
    public function searchPlugin
    (
        $pluginName
    );

    /**
     * Get plugin information
     *
     * @param $pluginId
     * @return PluginDescription
     */
    public function getPlugin
    (
        $pluginId
    );

    /**
     * Check if local copy of package list is available
     *
     * @return bool
     */
    public function hasPackageList();

    /**
     * Refresh cached package list from remote
     *
     * @return void
     */
    public function updatePackageList();

    /**
     * Get cached package list
     *
     * @return array
     */
    public function getPackageList();

}