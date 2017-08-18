<?php

namespace Scalar\Repository;

use Scalar\Cache\Cache;
use Scalar\Cache\Factory\FileCacheStorageFactory;
use Scalar\Cache\Factory\MemCacheStorageFactory;
use Scalar\Cache\Storage\MemCacheStorage;
use Scalar\Http\Client\CurlHttpClient;
use Scalar\Http\Factory\HttpClientFactory;
use Scalar\Http\Message\ResponseInterface;
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
            $memCacheStorageFactory = new MemCacheStorageFactory();
            $this->cache = new Cache($memCacheStorageFactory->createMemCacheStorage());
        } else {

            $fileStorageFactory = new FileCacheStorageFactory();
            $this->cache = new Cache($fileStorageFactory->createFileCacheStorage());
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

    private function createHttpRequest($path)
    {
        $httpClientFactory = new HttpClientFactory();
        $httpClient = $httpClientFactory->createHttpClient
        (
            new CurlHttpClient(),
            $this->uri->withPath($path)
        );


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