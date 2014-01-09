<?php
/**
 * Hello World - Min
 *
 * - fixed page resource
 * - no template engine
 * - page resource only
 */
// page request
$app = require dirname(dirname(__DIR__)) . '/bootstrap/instance.php';

$response = $app
    ->resource
    ->get
    ->uri('page://self/minhello')
    ->eager
    ->request();

echo $response->body . PHP_EOL;
