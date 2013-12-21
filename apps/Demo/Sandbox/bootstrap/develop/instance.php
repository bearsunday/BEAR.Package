<?php
/**
 * Return application instance with development setup
 *
 * @global $context
 *
 * @return BEAR\Sunday\Extension\Application\AppInterface
 */

use BEAR\Package\Dev\Dev;

ob_start();

// set application root as current directory
$appDir = dirname(dirname(__DIR__));

require $appDir . '/bootstrap/autoload.php';

// development configuration
$app = (new Dev($appDir))
    ->iniSet()
    ->loadDevFunctions()
    ->registerFatalErrorHandler()
    ->registerExceptionHandler("{$appDir}/var/log")
    ->registerSyntaxErrorEdit()
    ->getDevApplication($context);

return $app;
