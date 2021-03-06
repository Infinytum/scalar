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
 * Date: 8/19/17
 * Time: 11:39 PM
 */

namespace Scalar\Core\Updater;


use Scalar\Core\Scalar;
use Scalar\Repository\RepositoryManager;
use Scalar\Updater\Updater;

class CoreUpdater extends Updater
{

    public function __construct()
    {
        $scalarConfig = Scalar::getService(Scalar::SERVICE_CORE_CONFIG);
        /**
         * @var RepositoryManager $repoManager
         */
        $repoManager = Scalar::getService(Scalar::SERVICE_REPOSITORY_MANAGER);

        parent::__construct
        (
            $repoManager->getUpdateRepository(),
            $scalarConfig->asScalarArray()->getPath(Scalar::CONFIG_UPDATE_CHANNEL)
        );
    }

}