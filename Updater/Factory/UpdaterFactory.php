<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 08.06.17
 * Time: 19:32
 */

namespace Scalar\Updater\Factory;


use Scalar\Core\Config\ScalarConfig;
use Scalar\Repository\Repository;
use Scalar\Repository\RepositoryManager;
use Scalar\Updater\Updater;

class UpdaterFactory implements UpdaterFactoryInterface
{

    const CONFIG_UPDATE_CHANNEL = 'Updater.Channel';

    public function __construct()
    {
        ScalarConfig::getInstance()->setDefaultAndSave(self::CONFIG_UPDATE_CHANNEL, 'stable');
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
            ScalarConfig::getInstance()->get(self::CONFIG_UPDATE_CHANNEL)
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
            $channel = ScalarConfig::getInstance()->get(self::CONFIG_UPDATE_CHANNEL);
        }
        return new Updater
        (
            $repository,
            $channel
        );
    }
}