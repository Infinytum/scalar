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
 * Time: 23:50
 */

namespace Scalar\Repository;


use Scalar\Config\IniConfig;
use Scalar\Core\Scalar;
use Scalar\IO\Factory\UriFactory;
use Scalar\IO\File;
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
            Scalar::SERVICE_CORE_CONFIG
        );
        $this->scalarConfig->setDefaultPath(self::CONFIG_REPO_LIST, '{{App.Home}}/repository.list')
            ->setDefaultPath(self::CONFIG_REPO_DEFAULT, 'ScalarOfficial')
            ->setDefaultPath(self::CONFIG_REPO_UPDATE, 'ScalarOfficial');
        $this->iniConfig = new IniConfig(new File($this->scalarConfig->get(self::CONFIG_REPO_LIST), true));
        $this->iniConfig->load();

        $this->iniConfig->set('ScalarOfficial.Uri', 'https://repo.scaly.ch/v1')
            ->set('ScalarOfficial.ApiKey', false);
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