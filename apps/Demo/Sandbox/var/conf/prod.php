<?php

namespace Demo\Sandbox;

$appDir = dirname(dirname(__DIR__));

$sqlite = [
    'driver' => 'pdo_sqlite',
    'path' =>  $appDir . '/var/db/posts.sq3'
];

$config = [
    // database
    'master_db' => $sqlite,
    'slave_db' => $sqlite,
    // constants
    'app_name' => __NAMESPACE__,
    'tmp_dir' => $appDir . '/var/tmp',
    'log_dir' => $appDir . '/var/log',
    'lib_dir' => $appDir . '/var/lib',
];

return $config;
