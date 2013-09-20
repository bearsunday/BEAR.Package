<?php

use Ray\Di\Injector;
use Sandbox\Module\AppModule;

error_reporting(E_ALL);

// set application root as current directory
chdir(dirname(__DIR__));

// init
require_once 'scripts/load.php';

// set the application path into the globals so we can access
// it in the tests.
$GLOBALS['APP_DIR'] = dirname(__DIR__);

// set the resource client
$GLOBALS['RESOURCE'] = Injector::create([new AppModule('test')])->getInstance('\BEAR\Resource\ResourceInterface');
