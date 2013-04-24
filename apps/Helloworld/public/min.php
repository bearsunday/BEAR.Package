<?php
/**
 * Hello World - Min
 *
 * - fixed page resource
 * - no template engine
 * - page resource only
 */

// profiler
//require dirname(dirname(dirname(__DIR__))) . '/scripts/dev/profile.php';

// page request
$app = require dirname(__DIR__) . '/scripts/instance.php';
$response = $app->resource->get->uri('page://self/minhello')->eager->request();

echo $response->body;
