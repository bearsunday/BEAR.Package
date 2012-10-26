<?php
$root = require __DIR__ . '/../ini.php';

$path = $_POST['path'];
$file = file_get_contents($root. $path, FILE_USE_INCLUDE_PATH);
$moddate = date (DATE_RFC822, time($path));
if (function_exists('posix_getpwuid')) {
    $ownerInfo = posix_getpwuid(fileowner($root . $path));
} else {
    $ownerInfo['name'] = '';
}
$fileperms = substr(decoct(fileperms($root . $path)), 2);;
$isWritable = is_writable($root . $path);
$fileInfo = " - {$moddate} - {$ownerInfo['name']}({$fileperms})";
$save = $isWritable ? '<a href="#" id="save" style="color:blue" onClick="save(\'' . $path . '\')" >save</a><br />' : '<span style="color:red">Read Only</span>';
$info = pathinfo($root . $path);
$ext = $info['extension'];
$ext = ($ext === 'tpl') ? 'html' : $ext;
$result = json_encode(
            array('save' => $save,
            'info' => $info,
            'file' => $file,
            'file_info' => $fileInfo,
            'ext' => $ext,
            'read_only' => !$isWritable
            )
          );
echo $result;
