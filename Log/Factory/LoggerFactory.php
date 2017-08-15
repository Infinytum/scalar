<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 09.06.17
 * Time: 21:34
 */

namespace Scaly\Log\Factory;


use Scaly\IO\Factory\StreamFactory;
use Scaly\IO\Stream\StreamInterface;
use Scaly\Log\Logger;
use Scaly\Log\LoggerInterface;

class LoggerFactory implements LoggerFactoryInterface
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
    )
    {
        return new Logger($streamInterface);
    }

    /**
     * Create logger with file
     *
     * @param string $filePath
     * @return LoggerInterface
     */
    public function createLoggerFromFile
    (
        $filePath
    )
    {
        $streamFactory = new StreamFactory();
        return new Logger($streamFactory->createStreamFromFile($filePath, 'r+'));
    }
}