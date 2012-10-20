<?php
/**
 * Application config
 */
$appDir = dirname(__DIR__);

// @Named($key) => instance
$config = [
    // constants
    'app_name' => 'Helloworld',
    'app_dir' => $appDir,
    'tmp_dir' => $appDir . '/data/tmp',
    'log_dir' => $appDir . '/data/log'
];

return $config;
