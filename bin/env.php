#! /usr/bin/env php
<?php
/**
 * BEAR.Sunday install/env checker
 *
 * usage:
 *
 * $ php bin/env.php;
 *
 */

$ok = "\e[07;32m" . ' OK ' . "\033[0m";
$ng = "\e[07;31m" . ' NG ' . "\033[0m";

// PHP
$isPhpVersionOk = version_compare(phpversion(), '5.4', '>=') ? $ok : $ng;
echo '== Required ==' . PHP_EOL;
echo "{$isPhpVersionOk}PHP:" . phpversion() . PHP_EOL;

// vendor
$isVendorInstalledOk = file_exists(dirname(__DIR__) . '/vendor/composer/installed.json') ? $ok : $ng;
echo "{$isVendorInstalledOk}Vendor install" . PHP_EOL;

echo '== Optional ==' . PHP_EOL;

// APC
$apcVersion = phpversion("apc");
$isAPCVersionOk = version_compare(phpversion("apc"), '3.1.8', '>=') ? $ok : $ng;
echo "{$isAPCVersionOk}APC:" . phpversion("apc") . PHP_EOL;

// DB
$id = isset($_SERVER['BEAR_DB_ID']) ? $_SERVER['BEAR_DB_ID'] : 'root';
$password = isset($_SERVER['BEAR_DB_PASSWORD']) ? $_SERVER['BEAR_DB_PASSWORD'] : '';
try {
    $pdo = new \PDO("mysql:host=localhost; dbname=blogbeartest", $id, $password);
    $isDbConnectionOk = $ok;
} catch (Exception $e) {
    $isDbConnectionOk = $e->getMessage();
}
echo "{$isDbConnectionOk}DB connect({$id}/{$password})" . PHP_EOL;

echo '== Develop ===' . PHP_EOL;

$hasXhprof = phpversion("xhprof") ? $ok : $ng;
// options
echo "{$hasXhprof}xhprof: " . phpversion("xhprof") . '(' . ini_get('xhprof.output_dir') . ')' . PHP_EOL;

$hasXdebug = phpversion("Xdebug") ? $ok : $ng;
echo "{$hasXdebug}Xdebug: " . phpversion("Xdebug") . PHP_EOL;

$hasPdoSqlite = phpversion('pdo_sqlite') ? $ok : $ng;
echo "{$hasPdoSqlite}PDO-Sqlite: " . phpversion("pdo_sqlite") . PHP_EOL;

$isEnvOk = $isVendorInstalledOk === $ok
           && ($isDbConnectionOk === $ok);
$isInstallOk = $isEnvOk ? $ok : $ng;

echo PHP_EOL;
echo "BEAR.Sunday env check: {$isInstallOk}" . PHP_EOL . PHP_EOL;
return $isEnvOk;
