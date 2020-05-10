<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use BEAR\Package\Injector;
use BEAR\Sunday\Extension\Application\AppInterface;

return (new Injector('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/tmp'))->getInstance(AppInterface::class);
