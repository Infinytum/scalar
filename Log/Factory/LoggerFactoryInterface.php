<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 09.06.17
 * Time: 21:32
 */

namespace Scaly\Log\Factory;


use Scaly\IO\Stream\StreamInterface;
use Scaly\Log\LoggerInterface;

interface LoggerFactoryInterface
{

    /**
     * Create logger with stream
     *
     * @param StreamInterface $streamInterface
     * @return LoggerInterface
     */
    public function createLogger
    (
        $streamInterface
    );

    /**
     * Create logger with file
     *
     * @param string $filePath
     * @return LoggerInterface
     */
    public function createLoggerFromFile
    (
        $filePath
    );

}