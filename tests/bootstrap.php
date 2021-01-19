<?php

declare(strict_types=1);

use Koriym\Attributes\AttributeReader;
use Ray\ServiceLocator\ServiceLocator;

use function BEAR\Package\deleteFiles;

require dirname(__DIR__) . '/vendor/autoload.php';

@unlink(__DIR__ . '/Fake/fake-app/autoload.php');
@unlink(__DIR__ . '/Fake/fake-app/module.dot');
@unlink(__DIR__ . '/Fake/fake-app/preload.php');
@unlink(__DIR__ . '/Fake/fake-app/var/tmp/hal-app/app/.do_not_clear');

deleteFiles(__DIR__ . '/tmp');
deleteFiles(__DIR__ . '/Fake/fake-app/var/log');
deleteFiles(__DIR__ . '/Fake/fake-app/var/tmp');
deleteFiles(__DIR__ . '/Fake/fake-app/var/tmp/hal-app');

ini_set('error_log', __DIR__ . '/tmp/error_log.txt');

// no annotation in PHP 8
if (PHP_MAJOR_VERSION >= 8) {
    ServiceLocator::setReader(new AttributeReader());
}
