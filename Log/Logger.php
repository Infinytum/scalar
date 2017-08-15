<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 09.06.17
 * Time: 19:58
 */

namespace Scaly\Log;


use Scaly\IO\Stream\StreamInterface;

class Logger implements LoggerInterface
{

    /**
     * @var StreamInterface
     */
    private $logStream;

    public function __construct
    (
        $logStream
    )
    {
        $this->logStream = $logStream;
    }

    /**
     * Write log message to log
     *
     * @param string $logMessage
     * @return void
     */
    public function log
    (
        $logMessage
    )
    {

        $this->logStream->write($logMessage . PHP_EOL);

    }

}