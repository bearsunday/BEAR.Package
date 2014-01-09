<?php

use BEAR\Package\Dev\Dev;
use Ray\Di\Injector;
use Demo\Sandbox\Module\AppModule;

error_reporting(E_ALL);

// set application root as current directory
chdir(dirname(__DIR__));

// load
require_once 'bootstrap/autoload.php';

// enable debug print p($var);
(new Dev())->loadDevFunctions();

// set the application path into the globals so we can access it in the tests.
$GLOBALS['APP_DIR'] = dirname(__DIR__);

// set the resource client
$GLOBALS['RESOURCE'] = Injector::create([new AppModule('test')])->getInstance('\BEAR\Resource\ResourceInterface');
