# BEAR.Package

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Package.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Package)

BEAR.Package is a [BEAR.Sunday](https://github.com/bearsunday/BEAR.Sunday) resource oriented framework implementation package.

## Package Components
 * Bootstrap
 * AppInjector
 * Compiler
 * Modules
    * PackageModule 
    * ProdModule
    * ApiModule
    * CliModile
    * HalModule
 * Router
    * CliRouter
    * WebRouter
 * Error
    * DevVndErrorPage
    * ProdVndErrorPage
    * ErrorHandler  
 * Logger
    * Monologger with psr interface  
 * Transfer
    * CliResponder
 * Annotations
    * `@ReturnCreatedResource`

## Demo

```
cd demo
composer install
vendor/bin/phpunit
php bin/run.php
```
See more at [demo/README.md](https://github.com/bearsunday/BEAR.Package/tree/1.x/demo)

## Documentation

Documentation is available at http://bearsunday.github.io/.
