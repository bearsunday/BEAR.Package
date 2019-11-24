<?php

tideways_xhprof_enable();
require dirname(__DIR__) . '/autoload.php';
(require dirname(__DIR__) . '/bootstrap.php')('prod-app');
file_put_contents(
    sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '.myapplication.xhprof',
    serialize(tideways_xhprof_disable())
);
