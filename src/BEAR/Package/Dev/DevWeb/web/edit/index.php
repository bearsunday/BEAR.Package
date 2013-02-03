<?php

/**
 * Ace editor
 *
 * @package BEAR.Package
 * @global  $view
 * @see http://ace.ajax.org/
 */
use BEAR\Package\Dev\DevWeb\Editor\Editor;

$view = (new Editor)->getView();
include __DIR__ . '/view.php';
