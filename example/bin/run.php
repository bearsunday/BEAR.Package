<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use BEAR\Package\Bootstrap;

require dirname(__DIR__) . '/load.php';

$page = (new Bootstrap)
    ->getApp('MyVendor\MyApp', 'hal-app')
    ->resource
    ->get
    ->uri('page://self/api/user')(['id' => 1]);

echo $page->code . PHP_EOL;
echo (string) $page;
