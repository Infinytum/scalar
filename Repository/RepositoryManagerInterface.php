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