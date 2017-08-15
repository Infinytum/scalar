<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 07.06.17
 * Time: 23:50
 */

namespace Scaly\Repository;


use Scaly\Config\IniConfig;
use Scaly\Core\Config\ScalyConfig;
use Scaly\IO\Factory\UriFactory;
use Scaly\IO\UriInterface;

class RepositoryManager implements RepositoryManagerInterface
{

    const CONFIG_REPO_LIST = 'Repository.List';
    const CONFIG_REPO_DEFAULT = 'Repository.Default';
    const CONFIG_REPO_UPDATE = 'Repository.Update';

    /**
     * @var IniConfig
     */
    private $iniConfig;

    public function __construct()
    {
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_REPO_LIST, '{{App.Home}}/repository.list');
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_REPO_DEFAULT, 'ScalyOfficial');
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_REPO_UPDATE, 'ScalyOfficial');

        if (!file_exists(ScalyConfig::getInstance()->get(self::CONFIG_REPO_LIST))) {
            $iniConfig = new IniConfig(ScalyConfig::getInstance()->get(self::CONFIG_REPO_LIST));
            $iniConfig->set('ScalyOfficial.Uri', 'https://repo.scaly.ch/v1');
            $iniConfig->set('ScalyOfficial.ApiKey', false);
            $iniConfig->save();
        }

        $this->iniConfig = new IniConfig(ScalyConfig::getInstance()->get(self::CONFIG_REPO_LIST));
        $this->iniConfig->load();
    }

    /**
     * Add repository to repository manager
     *
     * @param RepositoryInterface $repository
     * @return void
     */
    public function addRepository
    (
        $repository
    )
    {
        if ($this->hasRepository($repository->getName())) {
            return;
        }

        if ($this->hasRepositoryByUri($repository->getUri())) {
            return;
        }

        $this->iniConfig->set($repository->getName() . 'Uri', $repository->getUri());
        $this->iniConfig->set($repository->getName() . 'ApiToken', $repository->getApiToken());
        $this->iniConfig->save();
    }

    /**
     * Check if repository already exists
     *
     * @param string $repositoryName
     * @return bool
     */
    public function hasRepository
    (
        $repositoryName
    )
    {
        return $this->iniConfig->has($repositoryName);
    }

    /**
     * Check if repository already exists
     *
     * @param UriInterface $repositoryUri
     * @return bool
     */
    public function hasRepositoryByUri
    (
        $repositoryUri
    )
    {
        return $this->iniConfig->asScalyArray()->where(
            function ($repoName, $repoSettings) use ($repositoryUri) {
                return $repoSettings['Uri'] == $repositoryUri->serialize();
            }
        )->any();
    }

    /**
     * Return default repository
     *
     * @return Repository
     */
    public function getDefaultRepository()
    {
        $defaultRepository = ScalyConfig::getInstance()->get(self::CONFIG_REPO_DEFAULT);
        if ($this->hasRepository($defaultRepository)) {
            return $this->getRepository($defaultRepository);
        }

        throw new \RuntimeException
        (
            'Default repository not defined in repository list'
        );
    }

    /**
     * Get repository by name
     *
     * @param string $repositoryName
     * @return Repository
     */
    public function getRepository
    (
        $repositoryName
    )
    {
        if (!$this->hasRepository($repositoryName)) {
            return null;
        }

        return $this->createRepositoryFromList($repositoryName, $this->iniConfig->get($repositoryName));
    }

    /**
     * @param string $repoName
     * @param array $repoListEntry
     * @return Repository
     */
    private function createRepositoryFromList
    (
        $repoName,
        $repoListEntry
    )
    {
        $uriFactory = new UriFactory();
        $repository = new Repository
        (
            $repoName,
            $uriFactory->createUri($repoListEntry['Uri']),
            isset($repoListEntry['ApiToken']) ? $repoListEntry['ApiToken'] : null
        );
        return $repository;
    }

    /**
     * Return which repository to use for core updates
     *
     * @return Repository
     */
    public function getUpdateRepository()
    {
        $defaultRepository = ScalyConfig::getInstance()->get(self::CONFIG_REPO_UPDATE);
        if ($this->hasRepository($defaultRepository)) {
            return $this->getRepository($defaultRepository);
        }

        throw new \RuntimeException
        (
            'Update repository not defined in repository list'
        );
    }

    /**
     * Delete repository by name
     *
     * @param string $repositoryName
     * @return void
     */
    public function deleteRepository
    (
        $repositoryName
    )
    {
        if (!$this->hasRepository($repositoryName)) {
            return;
        }

        $repository = $this->getRepository($repositoryName);
        $this->iniConfig->set($repository->getName(), null);
        $this->iniConfig->save();
    }

    /**
     * Delete repository by uri
     *
     * @param UriInterface $repositoryUri
     * @return void
     */
    public function deleteRepositoryByUri
    (
        $repositoryUri
    )
    {
        if (!$this->hasRepositoryByUri($repositoryUri)) {
            return;
        }

        $repository = $this->getRepositoryByUri($repositoryUri);
        $this->iniConfig->set($repository->getName(), null);
        $this->iniConfig->save();
    }

    /**
     * Get repository by uri
     *
     * @param UriInterface $repositoryUri
     * @return Repository
     */
    public function getRepositoryByUri
    (
        $repositoryUri
    )
    {

        if (!$this->hasRepositoryByUri($repositoryUri)) {
            return null;
        }

        $repoName = $this->iniConfig->asScalyArray()->where(
            function ($repoName, $repoSettings) use ($repositoryUri) {
                return $repoSettings['Uri'] == $repositoryUri->serialize();
            }
        )->select(
            function ($key) {
                return $key;
            }
        )->firstOrDefault();

        return $this->createRepositoryFromList($repoName, $this->iniConfig->get($repoName));
    }
}