<?php
$packageDir = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
require $packageDir . '/vendor/classpreloader/classpreloader/src/ClassPreloader/ClassLoader.php';
use ClassPreloader\ClassLoader;

$config = ClassLoader::getIncludes(
    function (ClassLoader $loader) {
        $loader->register();
        $mode = 'Prod';
        $app = require dirname(dirname(__DIR__)) . '/instance.php';
    }
);

// Add a regex filter that requires all classes to match the regex
// $config->addInclusiveFilter('/Foo/');

// Add a regex filter that requires that a class does not match the filter
$config->addExclusiveFilter('/Sandbox\/Module\/ProdModule/');
$config->addExclusiveFilter('/Doctrine\/Common\/Annotation/');
$config->addExclusiveFilter('/FirePHP/');

return $config;