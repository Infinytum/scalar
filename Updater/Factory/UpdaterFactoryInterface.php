<?php

namespace Scalar\Updater\Factory;

use Scalar\Repository\Repository;
use Scalar\Updater\UpdaterInterface;

interface UpdaterFactoryInterface
{

    /**
     * Create default core updater
     *
     * @return UpdaterInterface
     */
    public function createDefaultUpdater();

    /**
     * Create updater
     *
     * @param Repository $repository
     * @param string $channel
     * @return UpdaterInterface
     */
    public function createUpdater
    (
        $repository,
        $channel
    );

}