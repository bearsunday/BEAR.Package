<?php

declare(strict_types=1);

/**
 * Fail when a clover report leaves any statement unexecuted, naming the lines.
 *
 * usage: php script/coverage-gate.php <clover.xml>
 */

if ($argc !== 2) {
    fwrite(STDERR, 'usage: php script/coverage-gate.php <clover.xml>' . PHP_EOL);
    exit(1);
}

$clover = $argv[1];
if (! is_file($clover)) {
    fwrite(STDERR, sprintf('No clover report at "%s".', $clover) . PHP_EOL);
    exit(1);
}

$xml = simplexml_load_file($clover);
if ($xml === false) {
    fwrite(STDERR, sprintf('Cannot read the clover report at "%s".', $clover) . PHP_EOL);
    exit(1);
}
$uncovered = [];
$statements = 0;
foreach ($xml->xpath('//file') ?: [] as $file) {
    $name = (string) $file['name'];
    foreach ($file->line as $line) {
        if ((string) $line['type'] !== 'stmt') {
            continue;
        }

        $statements++;
        if ((int) $line['count'] !== 0) {
            continue;
        }

        $uncovered[] = sprintf('%s:%d', $name, (int) $line['num']);
    }
}

if ($uncovered === []) {
    printf('%d statements, all executed' . PHP_EOL, $statements);
    exit(0);
}

fwrite(STDERR, sprintf('%d of %d statements never ran:' . PHP_EOL, count($uncovered), $statements));
fwrite(STDERR, '  ' . implode(PHP_EOL . '  ', $uncovered) . PHP_EOL);
exit(1);
