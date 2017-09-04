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

/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 09.06.17
 * Time: 22:22
 */

namespace Scalar\Core\Log;


use Scalar\Core\Scalar;
use Scalar\IO\Factory\StreamFactory;
use Scalar\IO\Stream\StreamInterface;
use Scalar\Log\LoggerInterface;

class CoreLogger implements LoggerInterface
{

    const Debug = 0;
    const Verbose = 1;
    const Info = 2;
    const Warning = 3;
    const Error = 4;

    const CONFIG_CORE_LOG_ENABLED = 'Core.Logging';
    const CONFIG_CORE_LOG_FILE = 'Core.LogPath';
    const CONFIG_CORE_LOG_LEVEL = 'Core.LogLevel';
    const CONFIG_CORE_LOG_APPEND = 'Core.LogAppend';

    /**
     * @var StreamInterface
     */
    private $logStream;

    /**
     * @var array
     */
    private $logArray;

    /**
     * @var int
     */
    private $logLevel;

    public function __construct()
    {
        $scalarConfig = Scalar::getService
        (
            Scalar::SERVICE_SCALAR_CONFIG
        );
        $scalarConfig->setDefaultPath(self::CONFIG_CORE_LOG_ENABLED, true)
            ->setDefaultPath(self::CONFIG_CORE_LOG_APPEND, false)
            ->setDefaultPath(self::CONFIG_CORE_LOG_FILE, '{{App.Home}}/scalar.log')
            ->setDefaultPath(self::CONFIG_CORE_LOG_LEVEL, CoreLogger::Warning)
            ->save();

        $streamFactory = new StreamFactory();
        $logStream = null;

        if ($scalarConfig->get(self::CONFIG_CORE_LOG_ENABLED) === true) {
            $mode = $scalarConfig->get(self::CONFIG_CORE_LOG_APPEND) ? 'a+' : 'w+';
            $logStream = $streamFactory->createStreamFromFile
            (
                $scalarConfig->get(self::CONFIG_CORE_LOG_FILE),
                $mode
            );

            if (!$logStream) {
                $logStream = $streamFactory->createStream();
            }

        } else {
            $logStream = $streamFactory->createStream();
        }
        $this->logStream = $logStream;
        $this->logLevel = $scalarConfig->get(self::CONFIG_CORE_LOG_LEVEL);
        $this->logArray = [];
    }

    /**
     * Write log message to log
     *
     * @param string $logMessage
     * @param int|string $logLevel
     * @return void
     */
    public function log
    (
        $logMessage,
        $logLevel = 'LOG'
    )
    {
        $this->logLine($logMessage, $logLevel);
    }

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
        $this->logStream->write($logMessage . PHP_EOL);
        array_push($this->logArray, $logMessage);
    }

    public function d
    (
        $logMessage
    )
    {
        if ($this->logLevel <= self::Debug) {
            $this->logLine($logMessage, 'DEBUG');
        }
    }

    public function v
    (
        $logMessage
    )
    {
        if ($this->logLevel <= self::Verbose) {
            $this->logLine($logMessage, 'VERBOSE');
        }
    }

    public function i
    (
        $logMessage
    )
    {
        if ($this->logLevel <= self::Info) {
            $this->logLine($logMessage, 'INFO');
        }
    }

    public function w
    (
        $logMessage
    )
    {
        if ($this->logLevel <= self::Warning) {
            $this->logLine($logMessage, 'WARNING');
        }
    }

    public function e
    (
        $logMessage
    )
    {
        if ($this->logLevel <= self::Error) {
            $this->logLine($logMessage, 'ERROR');
        }
    }

    /**
     * @return array
     */
    public function getLogArray()
    {
        return $this->logArray;
    }


}