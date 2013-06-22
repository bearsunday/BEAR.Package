<?php

$dir = $_POST['dir'];

$files = scandir($dir);
natcasesort($files);
if (count($files) < 3) { /* The 2 accounts for . and .. */
    return;
}
$html = "<ul class=\"jqueryFileTree\" style=\"display: none;\">";

// All dirs
foreach ($files as $file) {
    if (file_exists($dir . $file) && $file != '.' && $file != '..' && is_dir($dir . $file)) {
        if ((substr($file, 0, 1) !== '.') && (substr($file, 0, 2) !== '__')) {
            $html .= "<li class=\"directory collapsed\"><a onclick=\"folder_select('";
            $html .= htmlspecialchars($dir . $file) . "');\" href=\"#\" rel=\"" . htmlspecialchars(
                $dir . $file
            ) . "/\">";
            $html .= htmlspecialchars($file) . "</a></li>";
        }
    }
}

// All files
foreach ($files as $file) {
    if (file_exists($dir . $file) && $file != '.' && $file != '..' && !is_dir($dir . $file)) {
        $ext = preg_replace('/^.*\./', '', $file);
        if (substr($file, 0, 1) !== '.') {
            $html .= "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlspecialchars($dir . $file);
            $html .= "\">" . htmlspecialchars($file) . '</a></li>';
        }
    }
}
$html .= '</ul>';

echo $html;
