<?php

$context = PHP_SAPI === 'cli' ? 'cli-hal-app' : 'hal-app';

$context = 'prod-app';
require __DIR__ . '/bootstrap.php';
