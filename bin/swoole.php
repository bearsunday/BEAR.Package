<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/vendor/bear/swoole/bootstrap.php')(
    'prod-app',       // context
    'MyVendor\MyProject',      // application name
    '127.0.0.1',          // IP
    '8080'                // port
));
