<?php
/**
 * Hello World - basic
 *
 * - fixed page resource
 * - no template engine
 * - page resource only
 */

use BEAR\Resource\Exception\ResourceNotFound;

$app = require dirname(dirname(__DIR__)) . '/bootstrap/instance.php';

try {
    $response = $app
        ->resource
        ->{strtolower($_SERVER['REQUEST_METHOD'])}
        ->uri('page://self' . $_SERVER['REQUEST_URI'])
        ->withQuery($_GET)
        ->eager
        ->request();
} catch (ResourceNotFound $e) {
    http_response_code(404);
    echo '404';
    exit(1);
}

// output
foreach ($response->headers as $header) {
    header($header);
}
echo $response->body . PHP_EOL;
