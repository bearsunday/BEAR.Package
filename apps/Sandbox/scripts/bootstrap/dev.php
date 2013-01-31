<?php

// Init
error_reporting(E_ALL);
ini_set('display_errors', 1);
//ini_set('xdebug.collect_params', '0');

$packageDir = dirname(dirname(dirname(dirname(__DIR__))));

// Load
require  $packageDir . '/scripts/debug_load.php';

// profiler
require  $packageDir . '/scripts/profile.php';

// set exception handler for development
set_exception_handler(include $packageDir . '/scripts/debugger/exception_handler.php');

// set fatal error handler
register_shutdown_function(include $packageDir . '/scripts/debugger/shutdown_error_handler.php');

