<?php
/**
 * Application clear script
 *
 * Clear:
 *  apc code cache
 *  apc user cache
 *  smarty compile script
 *  tmp files
 */

// APC Cache
if (function_exists('apc_clear_cache')) {
    apc_clear_cache('user');
    apc_clear_cache();
}

// tmp dir
$tmpDir = dirname(__DIR__) . '/data/tmp';
$rm = function ($dir) use (&$rm) {
    foreach(glob($dir . '/*') as $file) {
        is_dir($file) ? $rm($file) : unlink($file);
        @rmdir($file);
    }
};

$rm("{$tmpDir}/cache");
array_map('unlink', glob("{$tmpDir}/smarty/template_c/*.tpl.php"));
array_map('unlink', glob("{$tmpDir}/cache/*"));

unset($rm);
unset($tmpDir);
