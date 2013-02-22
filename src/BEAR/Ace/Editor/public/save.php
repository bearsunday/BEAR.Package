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
$contents = $_POST['contents'];
echo (new Editor)->setBasePath($base)->setPath($_POST['file'])->save($contents);
