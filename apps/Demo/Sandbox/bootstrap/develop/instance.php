<?php
/**
 * Return application instance with development setup
 *
 * @global $context
 *
 * @return BEAR\Sunday\Extension\Application\AppInterface
 */

use BEAR\Package\Dev\Dev;

// set application root as current directory

$appDir = dirname(dirname(__DIR__));
require $appDir . '/bootstrap/autoload.php';

$context = 'dev';
$app = require $appDir . '/bootstrap/instance.php';
$dev = new Dev;
// development configuration
$dev
    ->iniSet()
    ->loadDevFunctions()
    ->registerFatalErrorHandler()
    ->registerExceptionHandler("{$appDir}/var/log")
    ->registerSyntaxErrorEdit()
    ->setApp($app, $appDir)
    ->serviceDevWeb();

return $dev->isDirectStaticFileAccess() ? false : $app;
