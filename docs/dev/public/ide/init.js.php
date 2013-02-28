<?php

use BEAR\Package\Dev\DevWeb\Editor\FileTree;

$projectDir = '';

$root = require __DIR__ . '/ini.php';
$tree = new FileTree($root);
//$tree->tree('#container_id1', $files['page'], '<span class=\"tree_label\">Page</span>');
//$tree->tree('#container_id2', $files['ro'], '<span class=\"tree_label\">Resource</span>');
//$tree->tree('#container_id3', $files['view'], '<span class=\"tree_label\">View template</span>');
$tree->tree('#container_id', '/' , '<span class=\"tree_label\">Project</span>');
$initialOpeningFile =$root . '/App.php';
$js = $tree->getJsCode($initialOpeningFile);
echo $js;
