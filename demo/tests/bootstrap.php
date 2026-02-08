<?php

declare(strict_types=1);

namespace MyVendor\MyProject;

$loader = require dirname(__DIR__, 2) . '/vendor/autoload.php';
$loader->addPsr4(__NAMESPACE__, dirname(__DIR__) . '/src');
