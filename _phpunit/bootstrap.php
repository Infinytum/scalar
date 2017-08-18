<?php

include '../Core/ClassLoader/AutoLoader.php';
define('SCALY_CORE', dirname(getcwd()));

$autoloader = Scaly\Core\ClassLoader\AutoLoader::getInstance();
$autoloader->register();
$autoloader->addClassPath("Scaly\\", SCALY_CORE);