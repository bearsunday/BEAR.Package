<?php

echo 'BEAR.Package test started...' . PHP_EOL;
$phpunit = dirname(dirname(__DIR__)) . '/vendor/bin/phpunit';
passthru(
    $phpunit . ' --coverage-text --configuration ' . dirname(dirname(__DIR__)) . "/phpunit.xml.dist; "
);

echo 'application test started...' . PHP_EOL;

$vendorName = dirname(dirname(__DIR__)) . '/apps/Demo';
foreach (['Helloworld', 'Sandbox'] as $appName) {
    passthru(
        "$phpunit --coverage-text --configuration {$vendorName}.{$appName}/phpunit.xml.dist"
    );
}

echo 'all test completed.' . PHP_EOL;
