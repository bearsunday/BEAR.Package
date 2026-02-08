<?php
declare(strict_types=1);

namespace MyVendor\MyProject;

use BEAR\Package\Injector;
use BEAR\Resource\Method;
use BEAR\Sunday\Extension\Application\AppInterface;
use MyVendor\MyProject\Module\App;

require dirname(__DIR__) . '/vendor/autoload.php';

$context = getenv('CONTEXT') ?: (PHP_SAPI === 'cli' ? 'cli-hal-app' : 'hal-app');

$app = Injector::getInstance('MyVendor\MyProject', $context, dirname(__DIR__))->getInstance(AppInterface::class);
assert($app instanceof App);
$request = $app->router->match($GLOBALS, $_SERVER);
try {
    $app->resource->newRequest(
        Method::from($request->method), $request->path, $request->query
    )()->transfer($app->responder, $_SERVER);
    exit(0);
} catch (\Throwable $e) {
    $app->throwableHandler->handle($e, $request)->transfer();
    exit(1);
}
