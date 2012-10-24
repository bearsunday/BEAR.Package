<?php
require 'Panda.php';

$file = isset($_GET['file']) ?  $_GET['file'] : false;
$line = isset($_GET['line']) ? $_GET['line'] : 0;
if (!is_readable($file)) {
    Panda::message('404 Not found', "[{$file}] is not readable.");
    exit();
}

// set variable for view
$view = array();
$view['file_path'] = $file;
$view['line'] = $line;
$view['file_contents'] = file_get_contents($file);
$id = md5($file);
$view['mod_date'] = date (DATE_RFC822, filemtime($file));
$view['owner_info'] = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($file)) : array('name' => 'n/a');
$fileperms = substr(decoct(fileperms($file)), 2);;
$view['is_writable'] = is_writable($file);
$view['is_writable_label'] = $view['is_writable'] ? "" : " Read Only";
$view['auth']  = md5(session_id() . $id);

// render
include 'view.php';
?>
