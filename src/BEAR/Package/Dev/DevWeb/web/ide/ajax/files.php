<?php
//
// jQuery File Tree PHP Connector for BEAR
//
//
// Original Author : Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
//
$root = require __DIR__ . '/../ini.php';

$files = unserialize(urldecode($_POST['dir']));
echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
// All files
foreach ($files as $file) {
    $ext = preg_replace('/^.*\./', '', $file);
    $shortfileName = str_replace(dirname($file) . DIRECTORY_SEPARATOR, '', $file);
    echo "<li class=\"file ext_$ext\"><a href=\"#\" alt=\"{$file}\" rel=\"" . htmlspecialchars($file) . "\">" . htmlspecialchars($shortfileName) . "</a></li>";
}
echo "</ul><!-- tree -->";
?>