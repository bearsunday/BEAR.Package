<?php

$context = PHP_SAPI === 'cli-server' ? 'hal-app' : 'prod-hal-app';
require dirname(__DIR__) . '/bootstrap/bootstrap.php';
