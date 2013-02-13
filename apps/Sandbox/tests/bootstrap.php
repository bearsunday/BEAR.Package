<?php
// application root
chdir(dirname(__DIR__));

// init
require_once dirname(__DIR__) . '/scripts/bootstrap.php';
require dirname(__DIR__) . '/scripts/clear.php';

// set the application path into the globals so we can access
// it in the tests.
$GLOBALS['APP_DIR'] = dirname(__DIR__);