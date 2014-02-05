<?php

/**
 * Benchmark script (prod, "/hello/world", file cache)
 *
 * usage:
 *
 * Uncomment "require ...preload.php" in contexts/prod.php
 *
 * $ php ../bin/clear.php
 * $ php ../../../bin/compile.php ..
 * $ php -n ./benchmark.php
 * $ php -n ./benchmark.php
 *
 */
$GLOBALS['_SERVER']['REQUEST_METHOD'] ='GET';
$GLOBALS['_SERVER']['REQUEST_URI'] ='/hello/world';
$time = microtime(true);
register_shutdown_function(function() use ($time) {
        printf("memory: %s / %s bytes\ntime(1 concurrent): %f ms\ndeclared: %d classes\nincluded: %d files > include_files.txt\n",
            number_format(memory_get_usage()),
            number_format(memory_get_peak_usage()),
            (microtime(true) - $time) * 1000,
            count(get_declared_classes()),
            count(get_included_files())
        );
        file_put_contents(__DIR__ . '/include_files.txt', print_r(get_included_files(), true));
        file_put_contents(__DIR__ . '/classes.txt', print_r(get_declared_classes(), true));

    });
//require dirname(__DIR__) . '/bootstrap/instance.php';

require dirname(__DIR__) . '/bootstrap/contexts/prod.php';

//memory: 8,085,008 / 9,057,504 bytes
//time(1 concurrent): 36.084175 ms
//included: 38 files
//declared: 272 classes
