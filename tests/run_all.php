<?php

require __DIR__ . '/run_apps.php';

passthru(
    'phpunit --coverage-text --configuration ' . dirname(__DIR__) . "/phpunit.xml.dist"
);
