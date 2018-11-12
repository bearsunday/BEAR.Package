# BEAR.HelloworldBenchmark

## Installation

    pecl install swoole
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

    wrk -t4 -c10 -d10s http://127.0.0.1/
    wrk -t4 -c10 -d10s http://127.0.0.1:8080/
