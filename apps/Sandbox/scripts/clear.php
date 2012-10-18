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
apc_clear_cache('user');
apc_clear_cache();

// tmp dir
$tmpDir = dirname(__DIR__) . '/data/tmp';
array_map('unlink', glob("{$tmpDir}/smarty/template_c/*.tpl.php"));
array_map('unlink', glob("{$tmpDir}/payment*"));
unset($tmpDir);