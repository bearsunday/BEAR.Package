<?php
/**
 * Application config
 *
 * @package    Sandbox
 * @subpackage Module
 */
$id = isset($_SERVER['BEAR_DB_ID']) ? $_SERVER['BEAR_DB_ID'] : 'root';
$password = isset($_SERVER['BEAR_DB_PASSWORD']) ? $_SERVER['BEAR_DB_PASSWORD'] : '';

// slave db id
if (isset($_SERVER['BEAR_DB_ID_SLAVE'])) {
    $slaveId = $_SERVER['BEAR_DB_ID_SLAVE'];
} elseif (isset($_SERVER['BEAR_DB_ID'])) {
    $slaveId = $_SERVER['BEAR_DB_ID'];
} else {
    $slaveId = 'root';
}

// slave db password
if (isset($_SERVER['BEAR_DB_PASSWORD_SLAVE'])) {
    $slavePassword = $_SERVER['BEAR_DB_PASSWORD_SLAVE'];
} elseif (isset($_SERVER['BEAR_DB_PASSWORD'])) {
    $slavePassword = $_SERVER['BEAR_DB_PASSWORD'];
} else {
    $slavePassword = '';
}

$appDir = dirname(__DIR__);

// @Named($key) => instance
$config = [
    // database
    'master_db' => [
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'dbname' => 'blogbear',
        'user' => $id,
        'password' => $password,
        'charset' => 'UTF8'
    ],
    'slave_db' => [
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'dbname' => 'blogbear',
        'user' => $slaveId,
        'password' => $slavePassword,
        'charset' => 'UTF8'
    ],
    // constants
    'app_name' => 'Sandbox',
    'app_dir' => $appDir,
    'tmp_dir' => $appDir . '/data/tmp',
    'log_dir' => $appDir . '/data/log'
];

return $config;
