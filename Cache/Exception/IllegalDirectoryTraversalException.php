<?php

namespace Scaly\Cache\Exception;


class IllegalDirectoryTraversalException extends \Exception implements CacheStorageException
{

    const ILLEGAL_DIRECTORY_TRAVERSAL = 'Detected directory traversal! Bad key "%s"';

    public function __construct($message = "", $arguments, $code = 0, $throwable = null)
    {
        array_unshift($arguments, $message);
        $message = call_user_func_array("sprintf", $arguments);
        parent::__construct($message, $code, $throwable);
    }

}