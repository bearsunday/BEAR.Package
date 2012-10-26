<?php
//
// jQuery File Tree PHP Connector
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//

$root = require __DIR__ . '/../ini.php';

$_POST['dir'] = urldecode($_POST['dir']);

if (!(file_exists($root . $_POST['dir'])) ) {
} else {
}
$dir = $root . $_POST['dir'];
$files = scandir($dir);
natcasesort($files);
if( count($files) > 2 ) { /* The 2 accounts for . and .. */
    echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
    // All dirs
    foreach( $files as $file ) {
        if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file) ) {
            if ((substr($file, 0, 1) !== '.') && (substr($file, 0, 2) !== '__')) {
                $html =  "<li class=\"directory collapsed\"><a onclick=\"folder_select('" . htmlspecialchars($_POST['dir'] . $file) . "');\" href=\"#\" rel=\"" . htmlspecialchars($_POST['dir'] . $file) . "/\">" . htmlspecialchars($file) . "</a></li>";
                echo $html;
            }
        }
    }
    // All files
    foreach( $files as $file ) {
        if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
            $ext = preg_replace('/^.*\./', '', $file);
            if (substr($file, 0, 1) !== '.') {
                echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlspecialchars($_POST['dir'] . $file) . "\">" . htmlspecialchars($file) . "</a></li>";
            }
        }
    }
    echo "</ul>";
}
