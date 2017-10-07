<?php
/**
 * (C) 2017 by Michael Teuscher (mk.teuscher@gmail.com)
 * as part of the Scalar PHP framework
 *
 * Released under the AGPL v3.0 license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Scalar\Core\Service;


use Scalar\IO\Exception\IOException;
use Scalar\IO\Factory\StreamFactory;
use Scalar\IO\File;
use Scalar\Log\Logger;

class CoreLoggerService extends CoreService
{

    // Log Levels

    const DEBUG = 0;
    const VERBOSE = 1;
    const INFO = 2;
    const WARNING = 3;
    const ERROR = 4;

    // Configuration

    const CONFIG_CORE_LOG_ENABLED = 'Logging';
    const CONFIG_CORE_LOG_FILE = 'LogPath';
    const CONFIG_CORE_LOG_LEVEL = 'LogLevel';
    const CONFIG_CORE_LOG_APPEND = 'LogAppend';

    // Exceptions

    const ERR_KERNEL_LOG_NOT_WRITABLE = 'Could not access or create kernel.log with write permissions!';

    /**
     * Determines which log messages will be written in the log file
     * @var int
     */
    private $logLevel = self::WARNING;

    /**
     * Logger instance
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $logArray = [];


    /**
     * CoreLoggerService constructor.
     */
    public function __construct()
    {
        parent::__construct('Logger');
    }

    /**
     * Log message with level debug
     *
     * @param string $logMessage
     */
    public function d
    (
        $logMessage
    )
    {
        if ($this->logLevel <= self::DEBUG) {
            $this->logLine($logMessage, 'DEBUG');
        }
    }

    /**
     * Writes message to file and to log array
     *
     * @param string $logMessage
     * @param string $logLevel
     */
    private function logLine
    (
        $logMessage,
        $logLevel
    )
    {
        $callerInformation = debug_backtrace(false, 3)[2];
        $reflectionClass = new \ReflectionClass($callerInformation['class']);
        $className = $reflectionClass->getShortName();
        $logMessage = '[' . date("h:i:s") . '/' . $logLevel . '/' . $className . '::' . $callerInformation['function'] . '] ' . $logMessage;
        $this->logger->log($logMessage);
        array_push($this->logArray, $logMessage);
    }

    /**
     * Log message with level verbose
     *
     * @param string $logMessage
     */
    public function v
    (
        $logMessage
    )
    {
        if ($this->logLevel <= self::VERBOSE) {
            $this->logLine($logMessage, 'VERBOSE');
        }
    }

    /**
     * Log message with level warning
     *
     * @param string $logMessage
     */
    public function w
    (
        $logMessage
    )
    {
        if ($this->logLevel <= self::WARNING) {
            $this->logLine($logMessage, 'WARNING');
        }
    }

    /**
     * Log message with level error
     *
     * @param string $logMessage
     */
    public function e
    (
        $logMessage
    )
    {
        if ($this->logLevel <= self::ERROR) {
            $this->logLine($logMessage, 'ERROR');
        }
    }

    /**
     * Get all messages in the order they were logged
     * @return array
     */
    public function getLogArray()
    {
        return $this->logArray;
    }

    /**
     * Initialize service for work
     *
     * @param bool $kernelLogger Will log to core directory if true
     * @return bool
     */
    public function setup
    (
        $kernelLogger = true
    )
    {
        $this->addDefault(self::CONFIG_CORE_LOG_ENABLED, true);
        $this->addDefault(self::CONFIG_CORE_LOG_APPEND, false);
        $this->addDefault(self::CONFIG_CORE_LOG_FILE, '{{App.Home}}/scalar.log');
        $this->addDefault(self::CONFIG_CORE_LOG_LEVEL, self::WARNING);

        $this->logLevel = $this->getValue(self::CONFIG_CORE_LOG_LEVEL);

        if ($kernelLogger) {
            $logFile = new File
            (
                SCALAR_CORE . '/kernel.log',
                true,
                $this->getValue
                (
                    self::CONFIG_CORE_LOG_APPEND
                ) ? 'a+' : 'w+'
            );
        } else {
            $logFile = new File
            (
                $this->getValue
                (
                    self::CONFIG_CORE_LOG_FILE
                ),
                true,
                $this->getValue
                (
                    self::CONFIG_CORE_LOG_APPEND
                ) ? 'a+' : 'w+'
            );
        }

        try {

            if (!$logFile->exists()) {
                $logFile->create();
            }

            $logStream = $logFile->toStream();

            if ($logStream === null) {
                throw new IOException(self::ERR_KERNEL_LOG_NOT_WRITABLE);
            }

            $retVal = true;
        } catch (\Exception $ex) {
            $streamFactory = new StreamFactory();
            $logStream = $streamFactory->createStream();
            $retVal = false;
        }

        $this->logger = new Logger($logStream);
        $this->i('Logging to ' . $this->getValue(self::CONFIG_CORE_LOG_FILE) . ' has started...');

        return $retVal;
    }

    /**
     * Log message with level information
     *
     * @param string $logMessage
     */
    public function i
    (
        $logMessage
    )
    {
        if ($this->logLevel <= self::INFO) {
            $this->logLine($logMessage, 'INFO');
        }
    }

    /**
     * Destruct service and execute tearDown tasks
     *
     * @param bool $kernelLogger
     * @return bool
     */
    public function tearDown
    (
        $kernelLogger = false
    )
    {

        if ($kernelLogger) {

            $logFile = new File
            (
                SCALAR_CORE . '/kernel.log',
                true,
                'a+'
            );

            try {

                if (!$logFile->exists()) {
                    $logFile->create();
                }

                $logStream = $logFile->toStream();

                if ($logStream === null) {
                    throw new IOException(self::ERR_KERNEL_LOG_NOT_WRITABLE);
                }
            } catch (\Exception $ex) {
                $streamFactory = new StreamFactory();
                $logStream = $streamFactory->createStream();
            }

            $this->logger = new Logger($logStream);

            return true;
        }

        $this->i('Logger tearing down...');
        $this->logger->close();

        return true;
    }
}