<?php

$appDir = $GLOBALS['APP_DIR'];
$conf = require dirname(dirname(__DIR__)) . '/var/conf/test.php';
$db = new PDO("sqlite:{$conf['master_db']['path']}");

return $db;
