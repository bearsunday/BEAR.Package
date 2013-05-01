<?php
/**
 * Web IDE
 *
 * @global $appDir application directory
 * @global $root   view root directory
 */
$view['base'] = basename(__DIR__);
$root = $appDir;
$html = include __DIR__ . '/view.php';

echo $html;
