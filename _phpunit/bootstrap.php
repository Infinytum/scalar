<?php

include '../Core/ClassLoader/AutoLoader.php';
define('SCALAR_CORE', dirname(getcwd()));

$autoloader = Scalar\Core\ClassLoader\AutoLoader::getInstance();
$autoloader->register();
$autoloader->addClassPath("Scalar\\", SCALAR_CORE);