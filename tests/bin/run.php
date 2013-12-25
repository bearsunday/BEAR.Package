<?php

echo 'BEAR.Package test started...' . PHP_EOL;

passthru(
    'phpunit --coverage-text --configuration ' . dirname(dirname(__DIR__)) . "/phpunit.xml.dist; "
);

echo 'application test started...' . PHP_EOL;

$appDir = dirname(dirname(__DIR__)) . '/apps/Demo';
foreach (['Helloworld', 'Sandbox'] as $appName) {
    passthru(
        "phpunit --coverage-text --configuration {$appDir}/{$appName}/phpunit.xml.dist"
    );
}

echo 'all test completed.' . PHP_EOL;
