<?php

error_reporting(E_ALL);
ini_set('xdebug.max_nesting_level', 300);

// init
require_once dirname(__DIR__) . '/scripts/bootstrap.php';

// set the application path into the globals so we can access
// it in the tests.
$GLOBALS['APP_DIR'] = dirname(__DIR__);
