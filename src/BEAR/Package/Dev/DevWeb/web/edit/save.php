<?php

/**
 * Save contents
 *
 * @package BEAR.Package
 */
use BEAR\Package\Dev\DevWeb\Editor\Editor;

$log = (new Editor)->save();
error_log($log);
echo $log;
