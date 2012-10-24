<?php
$path = $_POST['path'];
$data = $_POST['data'];
$result = file_put_contents($path, $data, LOCK_EX | FILE_TEXT);
$msg = ($result === false) ? 'Save error..' : ' Saved successfully';
echo $msg . $path;
