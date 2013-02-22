<?php

$appDir = dirname(__DIR__) . '/apps';
foreach (['Helloworld', 'Sandbox'] as $appName) {
    passthru(
        "phpunit --coverage-text --configuration {$appDir}/{$appName}/phpunit.xml.dist"
    );
}
