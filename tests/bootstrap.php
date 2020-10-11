<?php

declare(strict_types=1);

use function Ray\Compiler\deleteFiles;

require dirname(__DIR__) . '/vendor/autoload.php';

unlink(__DIR__ . '/Fake/fake-app/autoload.php');
unlink(__DIR__ . '/Fake/fake-app/module.dot');
unlink(__DIR__ . '/Fake/fake-app/preload.php');

deleteFiles(__DIR__ . '/tmp');
deleteFiles(__DIR__ . '/Fake/fake-app/var/log');
deleteFiles(__DIR__ . '/Fake/fake-app/var/tmp');
deleteFiles(__DIR__ . '/Fake/fake-app/var/tmp/hal-app');
