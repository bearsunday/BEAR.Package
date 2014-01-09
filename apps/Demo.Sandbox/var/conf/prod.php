<?php

/**
 * @global $appDir
 */
namespace Demo\Sandbox;

$sqlite = [
    'driver' => 'pdo_sqlite',
    'path' =>  $appDir . '/var/db/posts.sq3'
];
// $masterId = $_SERVER['SANDBOX_MASTER_ID'];
// $masterPassword = $_SERVER['SANDBOX_MASTER_PASSWORD'];
// $slaveId =  $_SERVER['SANDBOX_SLAVE_ID'];
// $masterPassword = $_SERVER['SANDBOX_SLAVE_PASSWORD'];
// $mysql = [
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
//    ]
//];

$config = [
    // database
    'master_db' => $sqlite,
    'slave_db' => $sqlite,
    // constants
    'app_name' => __NAMESPACE__,
    'tmp_dir' => "{$appDir}/var/tmp",
    'log_dir' => "{$appDir}/var/log",
    'lib_dir' => "{$appDir}/var/lib",
];

return $config;
