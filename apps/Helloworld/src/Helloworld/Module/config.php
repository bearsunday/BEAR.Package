<?php
/**
 * Application config
 */
$appDir = dirname(__DIR__);

// @Named($key) => instance
$config = [
    // constants
    'app_name' => 'Helloworld',
    'tmp_dir' => $appDir . '/var/tmp',
    'log_dir' => $appDir . '/var/log'
];

return $config;
