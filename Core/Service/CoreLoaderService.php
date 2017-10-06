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

namespace Scalar\Core\Service;

require_once SCALAR_CORE . '/ClassLoader/AutoLoader.php';

use Scalar\ClassLoader\AutoLoader;

class CoreLoaderService extends CoreService
{

    /**
     * Low-level AutoLoader
     * @var AutoLoader
     */
    private $autoLoader;

    /**
     * CoreLoaderService constructor.
     */
    public function __construct()
    {
        parent::__construct('AutoLoader', false);
    }

    /**
     * Unregister a namespace for auto-loading
     *
     * @param $namespace
     */
    public function unregisterNamespace
    (
        $namespace
    )
    {
        $this->autoLoader->removeClassPath($namespace);
    }

    public function getAutoLoader()
    {
        return $this->autoLoader;
    }

    /**
     * Initialize service for work
     *
     * @return bool
     */
    public function setup()
    {
        $this->autoLoader = new AutoLoader();
        $this->autoLoader->register();

        $this->registerNamespace
        (
            "Scalar\\",
            SCALAR_CORE,
            true
        );

        return true;
    }

    /**
     * Register a namespace for auto-loading
     *
     * @param string $namespace Namespace which shall be auto-loaded
     * @param string $baseDirectory Path on file-system where the files are located
     * @param bool $prepend Determines if this namespace should be added in front of all others
     */
    public function registerNamespace
    (
        $namespace,
        $baseDirectory,
        $prepend = false
    )
    {
        $this->autoLoader->addClassPath($namespace, $baseDirectory, $prepend);
    }

    /**
     * Destruct service and execute tearDown tasks
     *
     * @return bool
     */
    public function tearDown()
    {
        // TODO: Gracefully remove autoloader from spl_autoloader queue
        return true;
    }
}