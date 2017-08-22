<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 14.06.17
 * Time: 16:10
 */

namespace Scalar\Core\Handler;


use Scalar\Core\ClassLoader\AutoLoader;

class ErrorHandler
{

    public function register()
    {
        AutoLoader::getInstance()->addClassPath('Whoops', SCALAR_CORE . '/_library/Whoops/');
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }

}