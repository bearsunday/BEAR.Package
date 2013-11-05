<?php

/**
 * New resource
 *
 * usage:
 *
 * $ php bin/new_res.php MyApp page://self/hello/world
 */
use BEAR\Package\Dev\Application\ApplicationReflector;

$appName = isset($argv[1]) ? $argv[1] : error();
$uri = isset($argv[2]) ? $argv[2] : error();
$appFile = dirname(__DIR__) . "/apps/{$appName}/bootstrap/instance.php";
if (!file_exists($appFile)) {
    error("Invalid application name: {$appName}");
}
$app = require $appFile;
$appReflector = new ApplicationReflector($app);
try {
    list($result, $filePath) = $appReflector->newResource($uri);
} catch (\Exception $e) {
    error($e->getMessage());
}
echo "new resource [$uri] was successfully made at [{$filePath}]" . PHP_EOL;

/**
 * @param string $msg
 */
function error($msg = 'Usage: php bin/new_res.php <app-name> <new-uri>')
{
    echo $msg . PHP_EOL;
    exit(1);
}
