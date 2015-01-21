<?php

namespace MyVendor\MyApp;

use BEAR\Package\Bootstrap;
use BEAR\Package\AppMeta;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Annotations\AnnotationRegistry;

load: {
    $dir = dirname(dirname(dirname(__DIR__)));
    $loader = require $dir . '/vendor/autoload.php';
    /** @var $loader \Composer\Autoload\ClassLoader */
    $loader->addPsr4(__NAMESPACE__ . '\\', dirname(__DIR__) . '/src');
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
}

route: {
    $context = isset($context) ? $context : 'app';
    $app = (new Bootstrap)->newApp(new AppMeta(__NAMESPACE__), $context, new ApcCache);
    /** @var $app \BEAR\Sunday\Extension\Application\AbstractApp */
    $request = $app->router->match($GLOBALS, $_SERVER);
}

try {
    // resource request
    $page = $app->resource
        ->{$request->method}
        ->uri($request->path)
        ->withQuery($request->query)
        ->request();
    /** @var $page \BEAR\Resource\Request */

    // representation transfer
    $page()->transfer($app->responder, $_SERVER);
    exit(0);
} catch (\Exception $e) {
    $app->error->handle($e, $request)->transfer();
    exit(1);
}
