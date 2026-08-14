<?php
declare(strict_types=1);

namespace MyVendor\MyProject;

use BEAR\Package\Injector;
use BEAR\Resource\Method;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Extension\Router\NullMatch;
use MyVendor\MyProject\Module\App;

require dirname(__DIR__) . '/vendor/autoload.php';

$context = getenv('CONTEXT') ?: (PHP_SAPI === 'cli' ? 'cli-hal-app' : 'hal-app');

$app = Injector::getInstance('MyVendor\MyProject', $context, dirname(__DIR__))->getInstance(AppInterface::class);
assert($app instanceof App);
// The router reads the request line, so it fails on client input: a request URI with no path,
// or a JSON body that does not parse. Those answer 400 only if the handler below sees them.
$request = new NullMatch();
try {
    $request = $app->router->match($GLOBALS, $_SERVER);
    $app->resource->newRequest(
        Method::from($request->method), $request->path, $request->query
    )()->transfer($app->responder, $_SERVER);
    exit(0);
} catch (\Throwable $e) {
    $app->throwableHandler->handle($e, $request)->transfer();
    exit(1);
}
