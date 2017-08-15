<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 14.06.17
 * Time: 16:10
 */

namespace Scaly\Core\Handler;


use Scaly\Core\ClassLoader\AutoLoader;

class ErrorHandler
{

    public function register()
    {
        AutoLoader::getInstance()->addClassPath('Whoops', SCALY_CORE . '/_library/Whoops/');
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }

}