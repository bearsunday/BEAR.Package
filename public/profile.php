<?php

tideways_xhprof_enable();
require dirname(__DIR__) . '/autoload.php';
(require dirname(__DIR__) . '/bootstrap.php')('prod-app');
$profile = '/tmp' . DIRECTORY_SEPARATOR . uniqid() . '.helloworld.xhprof';
file_put_contents(
    $profile,
    serialize(tideways_xhprof_disable())
);
var_dump($profile);
