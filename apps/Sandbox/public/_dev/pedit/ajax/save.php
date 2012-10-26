<?php 
require_once '../ini.php';

$path = _BEAR_EDIT_ROOT_PATH . $_POST['path'];
$data = $_POST['data'];
$result = file_put_contents($path, $data, LOCK_EX | FILE_TEXT);
$msg = ($result === false) ? 'save error..' : ' saved';
echo $msg;
