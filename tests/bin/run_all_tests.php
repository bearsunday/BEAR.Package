<?php

echo 'BEAR.Package test started...' . PHP_EOL;

passthru(
    'phpunit --coverage-text --configuration ' . dirname(dirname(__DIR__)) . "/phpunit.xml.dist; "
);

echo 'application test started...' . PHP_EOL;

require __DIR__ . '/run_apps_tests.php';


echo 'all test completed.' . PHP_EOL;
