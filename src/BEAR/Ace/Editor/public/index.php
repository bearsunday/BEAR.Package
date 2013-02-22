<?php
/**
 * Ace editor
 *
 * @package    BEAR.Package
 * @subpackage Dev
 *
 * @see http://ace.ajax.org/
 */
use BEAR\Ace\Editor\Editor;

$base = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
$line = isset($_GET['line']) ? $_GET['line'] : 0;
echo (new Editor)->setBasePath($base)->setPath($_GET['file'])->setLine($line);
