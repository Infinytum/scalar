<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 08.06.17
 * Time: 19:32
 */

namespace Scaly\Updater\Factory;


use Scaly\Core\Config\ScalyConfig;
use Scaly\Repository\Repository;
use Scaly\Repository\RepositoryManager;
use Scaly\Updater\Updater;

class UpdaterFactory implements UpdaterFactoryInterface
{

    const CONFIG_UPDATE_CHANNEL = 'Updater.Channel';

    public function __construct()
    {
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_UPDATE_CHANNEL, 'stable');
    }

    /**
     * Create default core updater
     *
     * @return Updater
     */
    public function createDefaultUpdater()
    {
        $repositoryManager = new RepositoryManager();
        return new Updater
        (
            $repositoryManager->getUpdateRepository(),
            ScalyConfig::getInstance()->get(self::CONFIG_UPDATE_CHANNEL)
        );
    }

    /**
     * Create updater
     *
     * @param Repository $repository
     * @param string $channel
     * @return Updater
     */
    public function createUpdater
    (
        $repository,
        $channel = null
    )
    {
        if ($channel == null) {
            $channel = ScalyConfig::getInstance()->get(self::CONFIG_UPDATE_CHANNEL);
        }
        return new Updater
        (
            $repository,
            $channel
        );
    }
}