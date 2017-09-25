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

namespace Scalar\Core\Router;

use Scalar\Config\JsonConfig;
use Scalar\Core\Config\ScalarConfig;
use Scalar\Core\Router\Hook\ControllerDependencyInjectionHook;
use Scalar\Core\Router\Hook\MethodFilterMiddleware;
use Scalar\Core\Router\Hook\RestControllerHook;
use Scalar\Core\Scalar;
use Scalar\IO\File;
use Scalar\Router\Router;
use Scalar\Template\Hook\TemplateHook;

class CoreRouter extends Router
{

    public function __construct()
    {
        /**
         * @var ScalarConfig $scalarConfig
         */
        $scalarConfig = Scalar::getService(Scalar::SERVICE_SCALAR_CONFIG);

        $routeMap = new JsonConfig
        (
            new File
            (
                $scalarConfig->get(Scalar::CONFIG_ROUTER_MAP),
                true
            )
        );

        parent::__construct
        (
            $routeMap,
            $scalarConfig->get(Scalar::CONFIG_ROUTER_CONTROLLER)
        );

        $this->addHandler(new MethodFilterMiddleware());
        $this->addHandler(new TemplateHook());
        $this->addHandler(new RestControllerHook());
        $this->addHandler(new ControllerDependencyInjectionHook());
    }

}