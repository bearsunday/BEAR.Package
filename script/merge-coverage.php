<?php

declare(strict_types=1);

/**
 * Merge --coverage-php reports into one clover file.
 *
 * The phar work is measured by two runs of the suite, because phar.readonly is INI_SYSTEM:
 * a process started with -d phar.readonly=0 packs archives, one started with =1 reaches the
 * guard that refuses to. Uploading both clover files does not work - the coverage service
 * keeps the last report for each file, so the second run's misses erase the first run's hits.
 * Merging here makes the union a single report.
 *
 * usage: php script/merge-coverage.php <clover-out> <coverage-php>...
 */

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;

require dirname(__DIR__) . '/vendor/autoload.php';

if ($argc < 3) {
    fwrite(STDERR, 'usage: php script/merge-coverage.php <clover-out> <coverage-php>...' . PHP_EOL);
    exit(1);
}

[, $output] = $argv;
$coverage = null;
foreach (array_slice($argv, 2) as $file) {
    if (! is_file($file)) {
        fwrite(STDERR, sprintf('No coverage report at "%s".', $file) . PHP_EOL);
        exit(1);
    }

    $report = require $file;
    assert($report instanceof CodeCoverage);
    if ($coverage === null) {
        $coverage = $report;
        continue;
    }

    $coverage->merge($report);
}

assert($coverage instanceof CodeCoverage);
(new Clover())->process($coverage, $output);
printf('Merged %d reports into %s' . PHP_EOL, $argc - 2, $output);
