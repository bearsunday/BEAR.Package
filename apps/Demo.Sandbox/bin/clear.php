<?php
/**
 * Application clear script
 */
namespace Demo\Sandbox;

require_once dirname(dirname(dirname(__DIR__))) . '/src/Bootstrap/clearApp.php';

$clearDirs = [
    dirname(__DIR__) . '/var/tmp'
];

\BEAR\Bootstrap\clearApp($clearDirs);
