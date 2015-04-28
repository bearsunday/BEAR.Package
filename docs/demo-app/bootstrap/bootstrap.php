<?php

/**
 * @global string $context
 */
namespace MyVendor\MyApp;

use BEAR\Package\Bootstrap;
use Doctrine\Common\Annotations\AnnotationRegistry;

load: {
    $loader = require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
    /* @var $loader \Composer\Autoload\ClassLoader */
    $loader->addPsr4(__NAMESPACE__ . '\\', dirname(__DIR__) . '/src');
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
}

route: {
    $app = (new Bootstrap)->getApp(__NAMESPACE__, $context);
    /* @var $app AbstractApp \BEAR\Sunday\Extension\Application\AbstractApp */
    $request = $app->router->match($GLOBALS, $_SERVER);
}

try {
    // resource request
    $page = $app->resource
        ->{$request->method}
        ->uri($request->path)
        ->withQuery($request->query)
        ->request();
    /* @var $page \BEAR\Resource\Request */

    // representation transfer
    $page()->transfer($app->responder, $_SERVER);
    exit(0);
} catch (\Exception $e) {
    $app->error->handle($e, $request)->transfer();
    exit(1);
}
