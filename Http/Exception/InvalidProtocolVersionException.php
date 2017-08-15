<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 18.05.17
 * Time: 23:24
 */

namespace Scaly\Http\Exception;


use Throwable;

class InvalidProtocolVersionException extends HttpException
{

    private $protocolVersion;

    public function __construct($protocolVersion, $code = 0, Throwable $previous = null)
    {
        $this->protocolVersion = $protocolVersion;
        parent::__construct('Illegal Protocol Version "' . $protocolVersion . '"!', $code, $previous);
    }

    /**
     * Get invalid Protocol Version
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }


}