<?php

namespace Scaly\Updater\Factory;

use Scaly\Repository\Repository;
use Scaly\Updater\UpdaterInterface;

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