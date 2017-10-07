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

use Scalar\Core\Scalar;

/**
 * Class CoreService
 *
 * Base definition of a core service
 *
 * @package Scalar\Core\Service
 */
abstract class CoreService
{

    /**
     * Scalar Core Configuration
     * @var CoreConfigurationService
     */
    private $scalarConfig;

    /**
     * Name of service for core configuration
     * @var string
     */
    private $serviceName;

    /**
     * Health status of this service
     * @var bool
     */
    private $healthy = true;

    /**
     * CoreService constructor.
     * @param string $serviceName
     * @param bool $configuration
     */
    public function __construct
    (
        $serviceName,
        $configuration = true
    )
    {
        if ($configuration && Scalar::getServiceMap()->hasService(Scalar::SERVICE_CORE_CONFIG)) {
            $this->scalarConfig = Scalar::getService(Scalar::SERVICE_CORE_CONFIG);
        }
        $this->serviceName = $serviceName;
        $this->healthy = $this->setup();
    }

    /**
     * Initialize service for work
     *
     * @return bool
     */
    public abstract function setup();

    /**
     * Set default value by path
     *
     * This is a shorthand for getCoreConfig()->setDefaultPath
     *
     * @param $path
     * @param $value
     */
    public function addDefault
    (
        $path,
        $value
    )
    {
        $this->scalarConfig->setDefault
        (
            $this->getCoreConfigPrefix() . $path,
            $value
        );
    }

    /**
     * Get Prefix for all config paths
     *
     * @return string
     */
    public function getCoreConfigPrefix()
    {
        return $this->serviceName . '.';
    }

    /**
     * Get value by path
     *
     * This is a shorthand for getCoreConfig()->get
     *
     * @param string $path
     * @param mixed $default
     * @return mixed
     */
    public function getValue
    (
        $path,
        $default = null
    )
    {
        return $this->scalarConfig->get
        (
            $this->getCoreConfigPrefix() . $path,
            $default
        );
    }

    /**
     * Set value by path
     *
     * This is a shorthand for getCoreConfig()->setPath
     *
     * @param string $path
     * @param mixed $value
     */
    public function setValue
    (
        $path,
        $value = null
    )
    {
        $this->scalarConfig->set
        (
            $this->getCoreConfigPrefix() . $path,
            $value
        );
    }

    /**
     * Get the Name of this service
     *
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @return bool
     */
    public function isHealthy()
    {
        return $this->healthy;
    }

    /**
     * Destruct service and execute tearDown tasks
     *
     * @return bool
     */
    public abstract function tearDown();

    /**
     * @return CoreConfigurationService
     */
    protected function getCoreConfig()
    {
        return $this->scalarConfig;
    }

}