<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 07.06.17
 * Time: 23:50
 */

namespace Scalar\Repository;


use Scalar\Config\IniConfig;
use Scalar\Core\Scalar;
use Scalar\IO\Factory\UriFactory;
use Scalar\IO\UriInterface;

class RepositoryManager implements RepositoryManagerInterface
{

    const CONFIG_REPO_LIST = 'Repository.List';
    const CONFIG_REPO_DEFAULT = 'Repository.Default';
    const CONFIG_REPO_UPDATE = 'Repository.Update';

    /**
     * @var IniConfig
     */
    private $iniConfig;

    private $scalarConfig;


    public function __construct()
    {
        $this->scalarConfig = Scalar::getService
        (
            Scalar::SERVICE_SCALAR_CONFIG
        );
        $this->scalarConfig->setDefaultAndSave(self::CONFIG_REPO_LIST, '{{App.Home}}/repository.list');
        $this->scalarConfig->setDefaultAndSave(self::CONFIG_REPO_DEFAULT, 'ScalarOfficial');
        $this->scalarConfig->setDefaultAndSave(self::CONFIG_REPO_UPDATE, 'ScalarOfficial');

        if (!file_exists($this->scalarConfig->get(self::CONFIG_REPO_LIST))) {
            $iniConfig = new IniConfig($this->scalarConfig->get(self::CONFIG_REPO_LIST));
            $iniConfig->set('ScalarOfficial.Uri', 'https://repo.scaly.ch/v1');
            $iniConfig->set('ScalarOfficial.ApiKey', false);
            $iniConfig->save();
        }

        $this->iniConfig = new IniConfig($this->scalarConfig->get(self::CONFIG_REPO_LIST));
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
        return $this->iniConfig->asScalarArray()->where(
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
        $defaultRepository = $this->scalarConfig->get(self::CONFIG_REPO_DEFAULT);
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
        $defaultRepository = $this->scalarConfig->get(self::CONFIG_REPO_UPDATE);
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

        $repoName = $this->iniConfig->asScalarArray()->where(
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