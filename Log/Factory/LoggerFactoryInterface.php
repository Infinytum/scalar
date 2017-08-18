<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 09.06.17
 * Time: 21:32
 */

namespace Scalar\Log\Factory;


use Scalar\IO\Stream\StreamInterface;
use Scalar\Log\LoggerInterface;

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