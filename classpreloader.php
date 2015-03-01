<?php

use BEAR\Package\AppMeta;
use BEAR\Package\Bootstrap;
use BEAR\Package\Provide\Representation\HalRenderer;
use BEAR\Sunday\Extension\Application\AbstractApp;
use BEAR\Sunday\Extension\Application\AppInterface;
use ClassPreloader\ClassLoader;
use FakeVendor\HelloWorld\Module\AppModule;
use Ray\Di\Injector;

require __DIR__ . '/vendor/bear/sunday/src/Annotation/Cache.php';

$loader = require __DIR__ . '/vendor/autoload.php';
$config = ClassLoader::getIncludes(function (ClassLoader $loader) {
    $loader->register();
    class_exists(AppMeta::class);
    class_exists(Bootstrap::class);
    class_exists(HalRenderer::class);
    $app = (new Injector(new AppModule()))->getInstance(AppInterface::class);
    /* @var $app AbstractApp */
    $page = $app->resource->get->uri('page://self/')->eager->request();
    (string) $page;
});

// Add a regex filter that requires all classes to match the regex
// $config->addInclusiveFilter('/Foo/');

// Add a regex filter that requires that a class does not match the filter
$config->addExclusiveFilter('/Target/');

return $config;
