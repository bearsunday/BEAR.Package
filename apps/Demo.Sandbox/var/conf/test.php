<?php
/**
 * @global $appDir
 */

$sqlite = [
    'driver' => 'pdo_sqlite',
    'path' =>  $appDir . '/var/db/test.posts.sq3'
];
return [
    'master_db' => $sqlite,
    'slave_db' =>$sqlite
];
