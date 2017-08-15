<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 19.05.17
 * Time: 18:57
 */

namespace Scaly\IO\Exception;


use Throwable;

class MalformedUriException extends IOException
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = 'Malformed URI: "' . $message . "' given'";
        parent::__construct($message, $code, $previous);
    }

}