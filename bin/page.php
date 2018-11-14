<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')(PHP_SAPI === 'cli' ? 'cli-hal-app' : 'hal-app'));
