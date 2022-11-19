<?php

declare(strict_types=1);

$time = microtime(true);
register_shutdown_function(
    static function () use ($time): void {
        printf(
            "Memory: %s / %s bytes\nTime: %f ms\nDeclared: %d classes\nIncluded: %d files > include_files.txt\n\n",
            number_format(memory_get_usage()),
            number_format(memory_get_peak_usage()),
            (microtime(true) - $time) * 1000,
            count(get_declared_classes()),
            count(get_included_files()),
        );
        file_put_contents(__DIR__ . '/include_files.txt', print_r(get_included_files(), true));
    },
);
