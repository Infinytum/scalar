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

namespace Scalar\Repository;

use Scalar\Cache\Cache;
use Scalar\Cache\Storage\MemCacheStorage;
use Scalar\Core\Scalar;
use Scalar\Http\Client\CurlHttpClient;
use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequest;
use Scalar\IO\UriInterface;
use Scalar\Plugin\Factory\PluginDescriptionFactory;
use Scalar\Plugin\PluginDescription;
use Scalar\Util\ScalarArray;

class Repository implements RepositoryInterface
{

    const CACHE_PACKAGE_LIST = 'Repository.Packages.';

    const REPO_AUTH_HEADER = 'ScalarCoreAuthKey';

    const REPO_PACKAGE_LIST = '/v1/packages';
    const REPO_PACKAGE_SEARCH = '/v1/search';
    const REPO_PACKAGE_INFO = '/v1/plugin/';

    /**
     * @var string
     */
    private $name;

    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * @var string
     */
    private $apiToken;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct
    (
        $name,
        $uri,
        $apiToken = null
    )
    {
        $this->name = $name;
        $this->uri = $uri;
        $this->apiToken = $apiToken;

        if (MemCacheStorage::isAvailable()) {
            $this->cache = Scalar::getService(Scalar::SERVICE_MEM_CACHE);
        } else {
            $this->cache = Scalar::getService(Scalar::SERVICE_FILE_CACHE);
        }
    }

    /**
     * Set remote repository name
     *
     * @param string $name
     * @return static
     */
    public function withName
    (
        $name
    )
    {
        $newInstance = clone $this;
        $newInstance->name = $newInstance;
        return $newInstance;
    }

    /**
     * Get remote repository URI
     *
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get repository instance with URI
     *
     * @param UriInterface $uri New URI
     * @return static
     */
    public function withUri
    (
        $uri
    )
    {
        $newInstance = clone $this;
        $newInstance->uri = $uri;
        return $newInstance;
    }

    /**
     * Get API token for remote repository
     *
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * Get repository instance with api token
     *
     * @param string $apiToken New api token
     * @return static
     */
    public function withApiToken
    (
        $apiToken
    )
    {
        $newInstance = clone $this;
        $newInstance->apiToken = $apiToken;
        return $newInstance;
    }

    /**
     * Search for Plugins by name
     *
     * @param $pluginName
     * @return ScalarArray
     */
    public function searchPlugin
    (
        $pluginName
    )
    {

        $packageList = $this->getPackageList();

        return $packageList->where(
            function ($key, $value) use ($pluginName) {
                $pluginName = strtolower($pluginName);
                $packagePluginName = strtolower($value["name"]);
                if (strpos($packagePluginName, $pluginName) !== false) {
                    return true;
                }
                return false;
            }
        );

    }

    /**
     * Get cached package list
     *
     * @return ScalarArray
     */
    public function getPackageList()
    {
        if (!$this->hasPackageList()) {
            $this->updatePackageList();
        }

        $array = $this->cache->get(self::CACHE_PACKAGE_LIST . $this->getName());

        return new ScalarArray($array);
    }

    /**
     * Check if local copy of package list is available
     *
     * @return bool
     */
    public function hasPackageList()
    {
        return $this->cache->has(self::CACHE_PACKAGE_LIST . $this->getName());
    }

    /**
     * Get remote repository name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Refresh cached package list from remote
     *
     * @return void
     */
    public function updatePackageList()
    {

        $httpClient = $this->createHttpRequest(Repository::REPO_PACKAGE_LIST);
        $httpClient->request();

        $response = $httpClient->getResponse();

        $json = $this->parseResponse($response);

        $this->cache->delete(Repository::CACHE_PACKAGE_LIST . $this->getName());
        $this->cache->set(Repository::CACHE_PACKAGE_LIST . $this->getName(), $json['packages']);
    }

    private function createHttpRequest($path, $method = 'GET')
    {
        /**
         * @var CurlHttpClient $httpClient
         */
        $httpClient = Scalar::getService(Scalar::SERVICE_HTTP_CLIENT);
        $serverRequest = new ServerRequest($method, $this->uri->withPath($path));
        $httpClient->setRequest($serverRequest);

        if ($this->apiToken) {
            $httpClient->setRequest
            (
                $httpClient->getRequest()->withAddedHeader
                (
                    self::REPO_AUTH_HEADER,
                    $this->apiToken
                )
            );
        }
        return $httpClient;
    }

    /**
     * @param ResponseInterface $response
     * @return ScalarArray
     */
    private function parseResponse($response)
    {

        if ($response->getStatusCode() != 200) {
            throw new \RuntimeException
            (
                'Could not fetch package list from API Server'
            );
        }

        $json = json_decode($response->getBody()->getContents(), true);

        if ($json['_repository'] != $this->name) {
            throw new \RuntimeException
            (
                'Remote repository name does not match local repository name!'
            );
        }

        $scalyJson = new ScalarArray($json);

        if ($scalyJson->containsPath('sha1')) {

            $sha1Field = $json["sha1_field"];
            $fieldContentString = json_encode($scalyJson->getPath($sha1Field));

            if (sha1($fieldContentString) != $scalyJson->getPath('sha1')) {
                throw new \RuntimeException
                (
                    'Could not verify package list from API Server! Possible security breach!'
                );
            }

        }

        return $scalyJson;
    }

    /**
     * Get plugin information
     *
     * @param $pluginId
     * @return PluginDescription
     */
    public function getPlugin
    (
        $pluginId
    )
    {

        $packageList = $this->getPackageList();

        $pluginInfo = $packageList->where(
            function ($key, $value) use ($pluginId) {
                return $key == $pluginId;
            }
        )->select(
            function ($key, $value) {
                return $value;
            }
        )->firstOrDefault();

        $pluginDescriptionFactory = new PluginDescriptionFactory();

        return $pluginDescriptionFactory->createPluginDescriptionFromPackage($pluginInfo);
    }
}