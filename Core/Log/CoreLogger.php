<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 09.06.17
 * Time: 22:22
 */

namespace Scaly\Core\Log;


use Scaly\IO\Stream\StreamInterface;
use Scaly\Log\LoggerInterface;

class CoreLogger implements LoggerInterface
{

    const Debug = 0;
    const Verbose = 1;
    const Info = 2;
    const Warning = 3;
    const Error = 4;

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

    public function __construct
    (
        $logStream,
        $logLevel
    )
    {
        $this->logStream = $logStream;
        $this->logLevel = $logLevel;
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
    public function getLogArray(): array
    {
        return $this->logArray;
    }


}