<?php

namespace Scaly\Updater;

interface UpdaterInterface
{

    /**
     * Check if an update is available
     *
     * @return bool
     */
    public function hasUpdate();

    /**
     * Download and apply update
     *
     * @return bool
     */
    public function executeUpdate();

    /**
     * Get update channel
     *
     * @return string
     */
    public function getChannel();

    /**
     * Set update channel
     *
     * @param string $channel
     * @return void
     */
    public function setChannel
    (
        $channel
    );


}