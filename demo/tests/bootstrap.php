<?php

declare(strict_types=1);

$loader = require dirname(__DIR__, 2) . '/vendor/autoload.php';
$loader->addPsr4('MyVendor\\MyProject\\', dirname(__DIR__) . '/src');
