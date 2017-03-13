<?php
namespace MyVendor\MyApp;

use BEAR\Package\Bootstrap;
use Doctrine\Common\Annotations\AnnotationRegistry;

load: {
    // require dirname(__DIR__) . '/var/lib/xhprof.php';
    $loader = require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
    /* @var $loader \Composer\Autoload\ClassLoader */
    $loader->addPsr4(__NAMESPACE__ . '\\', dirname(__DIR__) . '/src');
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
}
boot: {
    /* @global $context */
    $app = (new Bootstrap)->getApp(__NAMESPACE__, $context);
    $request = $app->router->match($GLOBALS, $_SERVER);
}
try {
    // resource request
    $page = $app->resource
        ->{$request->method}
        ->uri($request->path)
        ->withQuery($request->query)
        ->eager
        ->request();
    /* @var $page \BEAR\Resource\ResourceObject */
    $page->transfer($app->responder, $_SERVER);
    exit(0);
} catch (\Exception $e) {
    $app->error->handle($e, $request)->transfer();
    exit(1);
}
