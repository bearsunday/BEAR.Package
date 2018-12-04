<?php

declare(strict_types=1);

use BEAR\Package\Unlink;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/hash.php';

(new Unlink)->force(__DIR__ . '/tmp');
(new Unlink)->force(__DIR__ . '/Fake/fake-app/var/tmp');
(new Unlink)->force(__DIR__ . '/Fake/fake-app/var/log');
(new Unlink)->force(dirname(__DIR__) . '/var/tmp');
