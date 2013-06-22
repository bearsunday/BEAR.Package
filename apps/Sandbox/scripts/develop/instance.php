<?php
/**
 * Return application instance with development setup
 *
 * @global $mode
 *
 * @return BEAR\Sunday\Extension\Application\AppInterface
 */

use BEAR\Package\Dev\Dev;

// set application root as current directory
chdir(dirname(dirname(__DIR__)));

require 'scripts/bootstrap.php';

// development configuration
$app = (new Dev)
    ->iniSet()
    ->loadDevFunctions()
    ->registerFatalErrorHandler()
    ->registerExceptionHandler('/data/log')
    ->registerSyntaxErrorEdit()
    ->getDevApplication($mode);

return $app;
