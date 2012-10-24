<?php
$rootDir = require_once '../ini.php';

$file = realpath($_POST['file']);
$data = $_POST['contents'];

// readable ?
if (!is_readable($file)) {
    throw new \InvalidArgumentException("Not found. {$file} is not readable.");
}
// allow only under project
if (strpos($file, $rootDir) !== 0) {
    throw new \OutOfRangeException($fullPath);
}

$result = file_put_contents($file, $data, LOCK_EX | FILE_TEXT);
$msg = ($result === false) ? 'save error for ' . $path : ' saved';
echo $msg;
