<?php

use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

$name = 'MyVendor\MyProject';
$context = 'prod-app';
echo 'Compiled: ' . (new Compiler)($name, $context, dirname(__DIR__)) . PHP_EOL;
