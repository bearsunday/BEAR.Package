<?php
$path = $_POST['path'];
$file = file_get_contents($path, FILE_USE_INCLUDE_PATH);
$modDate = date(DATE_RFC822, time($path));
$ownerInfo['name'] = '';
$isWritable = is_writable($path);
$save = $isWritable ? '<a href="#" id="save" style="color:blue" onClick="save(\'' . $path . '\')" >save</a><br />' : '<span style="color:red">Read Only</span>';
$info = pathinfo($path);
$ext = $info['extension'];
$ext = ($ext === 'tpl') ? 'html' : $ext;
$result = json_encode(
    [
        'save' => $save,
        'info' => $info,
        'file' => $file,
        'file_info' => '',
        'ext' => $ext,
        'read_only' => !$isWritable
    ]
);

echo $result;
