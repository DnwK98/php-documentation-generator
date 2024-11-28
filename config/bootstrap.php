<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;

$dotenv = new Dotenv();
$dotenv->loadEnv(dirname(__DIR__) . '/.env');

if (isset($_SERVER['APP_DEBUG']) && $_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}


// Autoload projects from include directory
$includeDir = __DIR__ . "/../include";

foreach (scandir($includeDir) as $project) {
    if(in_array($project, ['.', '..']) || 0 === strpos($project, '.')) {
        continue;
    }
    $directoires = scandir($includeDir . "/" . $project);

    if(in_array("vendor", $directoires)) {
        require_once realpath("$includeDir/$project/vendor/autoload.php");
    }
}