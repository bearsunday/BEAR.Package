<?php

namespace MyVendor\MyApp;

use BEAR\Package\Bootstrap;
use BEAR\Package\AppMeta;
use Doctrine\Common\Annotations\AnnotationRegistry;
use BEAR\Sunday\Extension\Application\AbstractApp;
use BEAR\Resource\Request;
use Doctrine\Common\Cache\ApcCache;

// loader
require dirname((__DIR__)) . '/lib/xhprof.php';
$loader = require dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4(__NAMESPACE__ . '\\', dirname(dirname(__DIR__)) . '/src');
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$app = (new Bootstrap)->newApp(new AppMeta(__NAMESPACE__), 'app', new ApcCache);
/** @var $app AbstractApp */

$request = $app->router->match($GLOBALS);

try {
    // resource request
    $page = $app->resource
        ->{$request->method}
        ->uri($request->path)
        ->withQuery($request->query)
        ->request();
    /** @var $page Request */

    // representation transfer
    $page()->transfer($app->responder);

} catch (\Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo $code;
    error_log($e);
}
