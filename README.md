# BEAR.HelloworldBenchmark

A BEAR.Sunday hellworld project for benchmarking.

## Installation

    pecl install swoole
    (Add extension=swoole.so to your php.ini.)
    git clone https://github.com/bearsunday/BEAR.HelloworldBenchmark
    cd BEAR.HelloworldBenchmark
    composer install --no-dev
    composer compile
    
See more for the installation of swoole at [Swoole:Getting Started](https://www.swoole.co.uk/docs/get-started/installation).

## Setup

### Apache

Place `public/{index.php favicon.ico .htaccess}` into web document root. 

### Swoole

    php bin/swoole.php

## Benchmarking

Benchmarking Tool: [wrk](https://github.com/wg/wrk)

apache

    wrk -t4 -c100 -d10s http://127.0.0.1/

swoole

    wrk -t4 -c100 -d10s http://127.0.0.1:8080/

See the results at [https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki)

## Profile

Start profiler and viwer

    php -dzend_extension=tideways_xhprof.so -S 127.0.0.1:8090 -t public public/profile.php
    php -S 127.0.0.1:8091 xhprof-html/

Browse the profile

    curl http://127.0.0.1:8090/
    http://127.0.0.1:8091/index.php?dir=%2Ftmp
