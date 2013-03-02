#! /usr/bin/env php
<?php
/**
 * Setup
 *
 * clear/chmod app data folder
 *
 * $ php bin/setup.php
 */
$packageDir = dirname(__DIR__);
ob_start();

function chmodWritable($path)
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $item) {
        if (is_dir($item) && is_writable($item)) {
            chmod($item, 0777);
        }
    }
}

$iterator = new RecursiveDirectoryIterator($packageDir . '/apps');
foreach ($iterator as $dir) {
    if ($iterator->hasChildren()) {
        $dataDir = $dir . '/data';
        echo "chmod:{$dataDir}" . PHP_EOL;
        chmodWritable($dataDir);
        $clear = $dir . '/scripts/clear.php';
        if (file_exists($clear)) {
            echo "clear:{$clear}" . PHP_EOL;
            passthru("php {$clear}");
        }
    }
}
