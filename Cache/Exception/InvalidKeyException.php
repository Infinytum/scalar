<?php

namespace Scaly\Cache\Exception;

class InvalidKeyException extends \Exception implements InvalidArgumentException
{

    const INVALID_KEY_EXCEPTION = 'Invalid Key "%s" passed to Cache Layer';

    public function __construct($message = "", $arguments, $code = 0, $throwable = null)
    {
        array_unshift($arguments, $message);
        $message = call_user_func_array("sprintf", $arguments);
        parent::__construct($message, $code, $throwable);
    }

}