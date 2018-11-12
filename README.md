# BEAR.HelloworldBenchmark

## Installation

    composer install --no-dev
    composer dump-autoload --no-dev

### Setup

#### Apache

Place `public/{index.php favicon.ico .htaccess}` into web document root. 

#### Swoole

    php bin/swoole.php

### Benchmarking

Benchmarking Tool: [wrk](https://github.com/wg/wrk)

    wrk -t4 -c10 -d10s http://127.0.0.1/
    wrk -t4 -c10 -d10s http://127.0.0.1:8080/
