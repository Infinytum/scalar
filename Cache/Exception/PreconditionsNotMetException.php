<?php

namespace Scaly\Cache\Exception;

class PreconditionsNotMetException extends \Exception implements CacheStorageException
{

    const PRECONDITION_WRITE_PERMISSION = 'Cannot create directory "%s" due lack of permission!';
    const PRECONDITION_READ_PERMISSION = 'Missing read privilege on directory "%s"';

    public function __construct($message = "", $arguments, $code = 0, $throwable = null)
    {
        array_unshift($arguments, $message);
        $message = call_user_func_array("sprintf", $arguments);
        parent::__construct($message, $code, $throwable);
    }

}