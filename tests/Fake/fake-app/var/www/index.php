<?php

namespace FakeVendor\HelloWorld;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Bootstrap;
use Doctrine\Common\Cache\ApcCache;

route: {
    $app = (new Bootstrap)->newApp(new AppMeta(__NAMESPACE__), 'app', new ApcCache);
    /* @var $app \BEAR\Sunday\Extension\Application\AbstractApp */
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
} catch (\Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo $code;
    error_log($e);
}
