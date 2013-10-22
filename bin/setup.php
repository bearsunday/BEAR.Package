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
require $packageDir . '/src/BEAR/Package/CurrentPhpExecutable.php';
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
        $dataDir = $dir . '';
        echo "chmod:{$dataDir}" . PHP_EOL;
        chmodWritable($dataDir);
        $clear = $dir . '/bin/clear.php';
        if (file_exists($clear)) {
            echo "clear:{$clear}" . PHP_EOL;
            $executable = \BEAR\Package\CurrentPhpExecutable::getExecutable();
            $configFile = \BEAR\Package\CurrentPhpExecutable::getConfigFile();
            passthru(escapeshellarg($executable) . ($configFile === null ? '' : (' -c ' . escapeshellarg($configFile))) . ' ' . $clear);
        }
    }
}
