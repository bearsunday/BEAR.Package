# BEAR.Package

[![Latest Stable Version](https://poser.pugx.org/bear/package/v/stable.png)](https://packagist.org/packages/bear/package)[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/koriym/BEAR.Package/badges/quality-score.png?b=develop-2)](https://scrutinizer-ci.com/g/koriym/BEAR.Package/?branch=develop-2)
 [![Build Status](https://secure.travis-ci.org/koriym/BEAR.Package.png?branch=develop-2)] (https://travis-ci.org/koriym/BEAR.Package.svg?branch=develop-2)

## Introduction

BEAR.Package is a [BEAR.Sunday](https://github.com/koriym/BEAR.Sunday) resource oriented framework implementation package.

# Installation

    $ composer create-project bear/package:~1.0@dev {PACKAGE_PATH} 

## built-in web server

for demo app web page

    $ php -S 0.0.0.0:8080 -t docs/demo-app/var/www/

You can then open a browser and go to `http://0.0.0.0:8080` to see the "Hello BEAR.Sunday" json output.

## Virtual Host for Production

Set up a virtual host to point to the `{$PACKAGE_PATH}docs/demo-app/var/www` directory of the application.

# Console

### web access (page resource)

    $ cd docs/demo/MyVendor/MyApp/var/bootstrap
    $ php web.php get '/user?id=1'
    
    code: 200
    header:
    body:
    {
        "user1": {
            "id": "1",
            "friend_id": "f1"
        },
        "_links": {
            "self": {
                "href": "/user?id=1"
            }
        }
    }
    
### api access (api resource)

    $ cd docs/demo/MyVendor/MyApp/var/bootstrap
    $ php api.php get '/user?id=1'

    code: 200
    header:
    body:
    {
        "id": "1",
        "friend_id": "f1",
        "_links": {
            "self": {
                "href": "/user?id=1"
            },
            "friend": {
                "href": "/friend?id=f1"
            }
        }
    }
    
## Application Context

### built-in module

 * `api` for API application
 * `cli` for console application
 * `hal` for Hypertext Application Language application
 * `prod` for production

To run application, Include application invoke script with contexts value as `$context'.

```php
$context = 'prod-api-hal-app'
require 'pat/to/bootstrap.php';   
```

contexts example

 * `app` - "bare" JSON application 
 * `cli-app` - console application
 * `prod-hal-api-app` - HAL API application for production
 * `dev-html-app` - HTML application for development *1

*1) `dev` and `html` is not provided in this package.

### application module

Application (context) module is placed `src/Module` directory.
The most basic application module is `AppModule`.

```php
class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new PackageModule(new AppMeta('MyVendor\MyApp')));
    }
}
```

Let's make database application.
Bind PDO class to `PdoProvider` provider in `AppModule` first.

```php
$this->install(new PackageModule(new AppMeta('MyVendor\MyApp')));
$this->bind(\PDO::class)->toProvider(PdoProvider::class)->in(Scope::SINGLETON);
```

Provide `PdoProvider.php`

```php
class PdoProvider implements  ProviderInterface
{
    public function get()
    {
        return new \PDO('sqlite::memory:');
    }
}
```

Now you can use `PDO` object as a dependency anwhere in the application.

```php
public function __construct(\PDO $pdo)
{
    var_dump($pdo); // class PDO#7 (0) {}
}
```

You don't need further configuration for every new class. You are configuring dependency, Not dependent.

### extend built-in context module

Your application may need different configuration for `prod` context.
Install built-in module to use existing `prod` binding, then you bind yours for production.

```php
use BEAR\Package\Context\ProdModule as Production;
    
class ProdModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new Production);
        $this->bind(\PDO::class)->toProvider(ProductionPdoProvider::class)->in(Scope::SINGLETON);
    }
}
```

## Build status

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/koriym/BEAR.Sunday/badges/quality-score.png?b=develop-2)](https://scrutinizer-ci.com/g/koriym/BEAR.Sunday/?branch=develop-2)
[![Code Coverage](https://scrutinizer-ci.com/g/koriym/BEAR.Sunday/badges/coverage.png?b=develop-2)](https://scrutinizer-ci.com/g/koriym/BEAR.Sunday/?branch=develop-2)
[![Build Status](https://travis-ci.org/koriym/BEAR.Sunday.svg?branch=develop-2)](https://travis-ci.org/koriym/BEAR.Sunday?branch=develop-2)
**BEAR.Sunday** - [Resource Oriented Applications Framework](https://github.com/koriym/BEAR.Sunday)

 [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/koriym/Ray.Aop/badges/quality-score.png?b=develop-2)](https://scrutinizer-ci.com/g/koriym/Ray.Aop/) [![Code Coverage](https://scrutinizer-ci.com/g/koriym/Ray.Aop/badges/coverage.png?b=develop-2)](https://scrutinizer-ci.com/g/koriym/Ray.Aop/) [![Build Status](https://secure.travis-ci.org/koriym/Ray.Aop.png?b=develop-2)](http://travis-ci.org/koriym/Ray.Aop) **Ray.Aop** - [Aspect Oriented Framework](https://github.com/koriym/Ray.Aop)

 [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/koriym/Ray.Di/badges/quality-score.png?b=develop-2)](https://scrutinizer-ci.com/g/koriym/Ray.Di/) [![Code Coverage](https://scrutinizer-ci.com/g/koriym/Ray.Di/badges/coverage.png?b=develop-2)](https://scrutinizer-ci.com/g/koriym/Ray.Di/) [![Build Status](https://secure.travis-ci.org/koriym/Ray.Di.png?b=develop-2)](http://travis-ci.org/koriym/Ray.Di) **Ray.Di** - [Dependency Injection Framework](https://github.com/koriym/Ray.Di)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/koriym/BEAR.Resource/badges/quality-score.png?b=develop-2)](https://scrutinizer-ci.com/g/koriym/BEAR.Resource/?branch=develop-2) [![Code Coverage](https://scrutinizer-ci.com/g/koriym/BEAR.Resource/badges/coverage.png?b=develop-2)](https://scrutinizer-ci.com/g/koriym/BEAR.Resource/?branch=develop-2) [![Build Status](https://travis-ci.org/koriym/BEAR.Resource.svg?branch=develop-2)](https://travis-ci.org/koriym/BEAR.Resource)
**BEAR.Resource** - [Hypermedia Framework for Object as a Service](https://github.com/koriym/BEAR.Resource)

## Requirements

 * PHP 5.5+
