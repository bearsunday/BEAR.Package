<?php
/**
 * Web IDE
 *
 * @global $appDir application directory
 */
$view['base'] = basename(__DIR__);
$root = $appDir;
$html = include __DIR__ . '/view.php';
echo $html;
