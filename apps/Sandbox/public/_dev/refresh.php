<?php
// clear APC cache
error_log('app files cleared by ' . __FILE__);
require dirname(dirname(__DIR__)) . '/scripts/clear.php';

header('Location: /');
