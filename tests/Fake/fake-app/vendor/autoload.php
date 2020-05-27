<?php

use Composer\Autoload\ClassLoader;

$loader =  require (dirname(__DIR__, 4)) . '/vendor/autoload.php';
assert($loader instanceof ClassLoader);
$loader->addPsr4('FakeVendor\\HelloWorld\\', dirname(__DIR__, 1) . '/src');

return $loader;
