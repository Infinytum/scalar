<?php

include '../Core/ClassLoader/AutoLoader.php';
define('SCALAR_CORE', dirname(getcwd()));

include SCALAR_CORE . '/Core/Scalar.php';
$autoloader = new \Scalar\Core\ClassLoader\AutoLoader();
$autoloader->register();
$autoloader->addClassPath("Scalar\\", SCALAR_CORE);