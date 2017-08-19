<?php
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
        $scalarConfig = Scalar::getService(Scalar::SERVICE_SCALAR_CONFIG);
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