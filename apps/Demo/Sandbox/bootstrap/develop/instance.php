<?php
/**
 * Return application instance with development setup
 *
 * @global $context
 *
 * @return BEAR\Sunday\Extension\Application\AppInterface
 */

use BEAR\Package\Dev\Dev;

umask(0);
ini_set('xdebug.max_nesting_level', 200);
ini_set('display_errors', 0);
ob_start();

// set application root as current directory
chdir(dirname(dirname(__DIR__)));

require 'bootstrap/autoload.php';

// development configuration
$logDir = dirname(dirname(__DIR__)) . '/var/log';
$app = (new Dev)
    ->iniSet()
    ->loadDevFunctions()
    ->registerFatalErrorHandler()
    ->registerExceptionHandler($logDir)
    ->registerSyntaxErrorEdit()
    ->getDevApplication($context);

return $app;
