<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

use BEAR\Ace\Editor;
use BEAR\Ace\Exception;

// config
$rootPath = dirname(dirname(dirname(dirname(__DIR__))));

try {
    $editor = (new Editor)->setRootPath($rootPath)->handle($_GET, $_POST, $_SERVER);
    echo $editor;
} catch (Exception $e) {
    http_response_code($e->getCode());
    echo $e->getCode();
}