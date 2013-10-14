<?php

namespace Sandbox;

$id = isset($_SERVER['BEAR_DB_ID']) ? $_SERVER['BEAR_DB_ID'] : 'root';
$password = isset($_SERVER['BEAR_DB_PASSWORD']) ? $_SERVER['BEAR_DB_PASSWORD'] : '';

$slaveId = isset($_SERVER['BEAR_DB_ID_SLAVE']) ? $_SERVER['BEAR_DB_ID_SLAVE'] : 'root';
$slavePassword = isset($_SERVER['BEAR_DB_PASSWORD_SLAVE']) ? $_SERVER['BEAR_DB_PASSWORD_SLAVE'] : '';

$appDir = dirname(dirname(dirname(dirname(__DIR__))));
// @Named($key) => instance

$sqlite = [
    'driver' => 'pdo_sqlite',
    'path' =>  $appDir . '/var/db/posts.sq3'
];

$config = [
    // database
    'master_db' => $sqlite,
    'slave_db' => $sqlite,
    // constants
    'tmp_dir' => $appDir . '/var/tmp',        // set faster storage if possible
    'log_dir' => $appDir . '/var/log',
    'vendor_dir' => $appDir . '/var/lib',
];

//$config = [
//    // database
//    'master_db' => [
//        'driver' => 'pdo_mysql',
//        'host' => 'localhost',
//        'dbname' => 'blogbear',
//        'user' => $id,
//        'password' => $password,
//        'charset' => 'UTF8'
//    ],
//    'slave_db' => [
//        'driver' => 'pdo_mysql',
//        'host' => 'localhost',
//        'dbname' => 'blogbear',
//        'user' => $slaveId,
//        'password' => $slavePassword,
//        'charset' => 'UTF8'
//    ],
//    // constants
//    'tmp_dir' => $appDir . '/var/tmp',        // set faster storage if possible
//    'log_dir' => $appDir . '/var/log',
//    'vendor_dir' => $appDir . '/var/lib',
//];

return $config;
