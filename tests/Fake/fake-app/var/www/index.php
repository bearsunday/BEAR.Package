<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Bootstrap;
use Doctrine\Common\Cache\ApcuCache;

$app = (new Bootstrap)->newApp(new AppMeta(__NAMESPACE__), 'app', new ApcuCache);
$request = $app->router->match($GLOBALS, $_SERVER);

try {
    $page = $app->resource->{$request->method}->uri($request->path)($request->query);
    // representation transfer
    $page->transfer($app->responder, $_SERVER);
} catch (\Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo $code;
    error_log((string) $e);
}
