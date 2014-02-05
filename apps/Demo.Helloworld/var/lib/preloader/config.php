<?php

use ClassPreloader\ClassLoader;
use BEAR\Package\Dev\Application\ApplicationReflector;

$appDir = dirname(dirname(dirname(__DIR__)));

$config = ClassLoader::getIncludes(
    function (ClassLoader $loader) use ($appDir) {
        $loader->register();
        $app = require $appDir . '/bootstrap/instance.php';
        (new ApplicationReflector($app))->compileAllResources();
    }
);

// Add a regex filter that requires that a class does not match the filter
$config
    ->addExclusiveFilter('/src\/Dev/')
    ->addExclusiveFilter('/Doctrine\/Common\/Annotation/')
    ->addExclusiveFilter('/FirePHP/')
    ->addExclusiveFilter('/PHPParser_*/')
    ->addExclusiveFilter('/TokenParser/');

return $config;
