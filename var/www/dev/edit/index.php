<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

use BEAR\Ace\Editor;
use BEAR\Ace\Exception;

// config
$packageRootPath = dirname(dirname(dirname(dirname(__DIR__))));
if (strpos(__DIR__, '/vendor/bear/package') !== false) {
    $packageRootPath = explode('/vendor/bear/package', __DIR__)[0];
}

try {
    $editor = (new Editor)->setRootPath($packageRootPath)->handle($_GET, $_POST, $_SERVER);
    echo $editor;
} catch (Exception $e) {
    http_response_code($e->getCode());
    echo $e->getCode();
}
