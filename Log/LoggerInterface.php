<?php

namespace Scaly\Log;

interface LoggerInterface
{

    /**
     * Write log message to log
     *
     * @param string $logMessage
     * @return void
     */
    public function log
    (
        $logMessage
    );

}