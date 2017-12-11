# MyVendor.MyProject

## Installation

    composer install

## Usage

### Run server

    COMPOSER_PROCESS_TIMEOUT=0 composer serve

### Console

    composer web get /
    composer api get /

### QA

    composer test       // phpunit
    composer coverage   // test coverate
    composer cs         // lint
    composer cs-fix     // lint fix
    vendor/bin/phptest  // test + cs
    vendor/bin/phpbuild // phptest + doc + qa
