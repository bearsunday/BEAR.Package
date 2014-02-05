<?php
/**
 * Hello World - basic
 *
 * - fixed page resource
 * - no template engine
 * - page resource only
 */

$app = require dirname(dirname(__DIR__)) . '/bootstrap/instance.php';

$response = $app
    ->resource
    ->get
    ->uri('page://self/hello')
    ->withQuery($_GET)
    ->eager
    ->request();

// output
foreach ($response->headers as $header) {
    header($header);
}
echo $response->body . PHP_EOL;
