<?php

namespace Scalar\Repository;

use Scalar\IO\UriInterface;

interface RepositoryManagerInterface
{

    /**
     * Add repository to repository manager
     *
     * @param RepositoryInterface $repository
     * @return void
     */
    public function addRepository
    (
        $repository
    );

    /**
     * Check if repository already exists
     *
     * @param string $repositoryName
     * @return bool
     */
    public function hasRepository
    (
        $repositoryName
    );

    /**
     * Check if repository already exists
     *
     * @param UriInterface $repositoryUri
     * @return bool
     */
    public function hasRepositoryByUri
    (
        $repositoryUri
    );

    /**
     * Get repository by name
     *
     * @param string $repositoryName
     * @return RepositoryInterface
     */
    public function getRepository
    (
        $repositoryName
    );

    /**
     * Get repository by uri
     *
     * @param UriInterface $repositoryUri
     * @return RepositoryInterface
     */
    public function getRepositoryByUri
    (
        $repositoryUri
    );

    /**
     * Return default repository
     *
     * @return RepositoryInterface
     */
    public function getDefaultRepository();

    /**
     * Return which repository to use for core updates
     *
     * @return RepositoryInterface
     */
    public function getUpdateRepository();

    /**
     * Delete repository by name
     *
     * @param string $repositoryName
     * @return void
     */
    public function deleteRepository
    (
        $repositoryName
    );

    /**
     * Delete repository by uri
     *
     * @param UriInterface $repositoryUri
     * @return void
     */
    public function deleteRepositoryByUri
    (
        $repositoryUri
    );

}