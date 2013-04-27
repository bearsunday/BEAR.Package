<?php

use BEAR\Ace\Editor;

if (! isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    throw new \BadMethodCallException;
}

$post = $_POST;
try {
    $log = (new Editor)->setRootPath('/')->setPath($post['file'])->save($post['contents']);
    echo $log;
} catch (\BEAR\Ace\Exception $e) {
    http_response_code($e->getCode());
    echo $e;
}
