#!/usr/bin/env php
<?php
/**
 * Env checker
 *
 * usage: $ php bin/env.php;
 */
$ok = '[OK] ';
$ng = '[NG] ';

echo '# Required' . PHP_EOL;
$isPhpVersionOk = version_compare(phpversion(), '5.4', '>=') ? $ok : $ng;
echo "{$isPhpVersionOk}PHP: " . phpversion() . PHP_EOL;
$installedJson = dirname(__DIR__) . '/vendor/composer/installed.json';
$isVendorInstalledOk = file_exists($installedJson) ? $ok : $ng;
echo "{$isVendorInstalledOk}Vendor install" . PHP_EOL;

echo '# Optional' . PHP_EOL;
$apcVersion = phpversion("apc");
$isAPCVersionOk = version_compare(phpversion("apc"), '3.1.8', '>=') ? $ok : $ng;
echo "{$isAPCVersionOk}APC: " . phpversion("apc") . PHP_EOL;

echo '# Develop' . PHP_EOL;
$hasXhprof = phpversion("xhprof") ? $ok : $ng;
$hasXdebug = phpversion("Xdebug") ? $ok : $ng;
echo "{$hasXdebug}Xdebug: " . phpversion("Xdebug") . PHP_EOL;
$hasPdoSqlite = phpversion('pdo_sqlite') ? $ok : $ng;
echo "{$hasPdoSqlite}PDO-Sqlite: " . phpversion("pdo_sqlite") . PHP_EOL;
echo "{$hasXhprof}xhprof: " . phpversion("xhprof") . PHP_EOL;
$isEnvOk = $isVendorInstalledOk === $ok;
$isInstallOk = $isEnvOk ? $ok : $ng;

echo PHP_EOL;
echo "BEAR.Sunday env check: {$isInstallOk}" . PHP_EOL . PHP_EOL;
