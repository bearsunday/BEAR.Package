<?php
// Web server script for production

use BEAR\Sunday\Router\Router;
use Ray\Di\Exception\NotReadable as NotFound;
use BEAR\Resource\Exception\Parameter as BadRequest;

// Profile
// require dirname(dirname(dirname(__DIR__))) . '/scripts/profile.php';

// Clear

// Application instance with loader
$mode = 'Prod';
$app = require dirname(__DIR__) . '/scripts/instance.php';

// Dispatch
list($method, $pagePath, $query) = $app->router->match($GLOBALS);

// Request
try {
    $app->page = $app->resource->$method->uri('page://self/' . $pagePath)->withQuery($query)->eager->request();
} catch (NotFound $e) {
    $code = 404;
    goto ERROR;
} catch (BadRequest $e) {
    $code = 400;
    goto ERROR;
} catch (Exception $e) {
    $code = 503;
    error_log((string)$e);
    goto ERROR;
}

// Transfer

OK: {
    $app->response->setResource($app->page)->render()->prepare()->send();
    exit(0);
}

ERROR: {
    http_response_code($code);
    require dirname(__DIR__) . "/http/{$code}.php";
    exit(1);
}
