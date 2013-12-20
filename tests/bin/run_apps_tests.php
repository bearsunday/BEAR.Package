<?php

$appDir = dirname(dirname(__DIR__)) . '/apps/Demo';
foreach (['Helloworld', 'Sandbox'] as $appName) {
    passthru(
        "phpunit --coverage-text --configuration {$appDir}/{$appName}/phpunit.xml.dist"
    );
}
