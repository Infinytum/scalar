<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 06.06.17
 * Time: 15:54
 */

namespace Scaly\Repository;


use Scaly\IO\UriInterface;
use Scaly\Plugin\PluginDescription;

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