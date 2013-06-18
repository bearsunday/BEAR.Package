<?php

use Ray\Di\Injector;
use Sandbox\Module\AppModule;

error_reporting(E_ALL);
ini_set('xdebug.max_nesting_level', 300);

// set application root as current directory
chdir(dirname(__DIR__));

// init
require_once 'scripts/bootstrap.php';

// set the application path into the globals so we can access
// it in the tests.
$GLOBALS['APP_DIR'] = dirname(__DIR__);
$GLOBALS['RESOURCE'] = Injector::create([new AppModule('test')])->getInstance('\BEAR\Resource\Resource');
