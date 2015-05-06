# BEAR.Package

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Package.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Package)

## Introduction

BEAR.Package is a [BEAR.Sunday](https://github.com/bearsunday/BEAR.Sunday) resource oriented framework implementation package.

# Installation

    $ composer create-project bear/package:~1.0@dev {$Vendor.$Package}

## built-in web server

for demo app web page

    $ php -S 127.0.0.1:8080 -t docs/demo-app/var/www/

You can then open a browser and go to `http://127.0.0.1:8080` to see the `{"greeting":"Hello BEAR.Sunday"}` json output.

## Virtual Host for Production

Set up a virtual host to point to the `/path/to/package/docs/demo-app/var/www` directory of the application.

## Demo - Hypermedia Application Language (HAL)

### ResourceObject
[src/Resource/App/User.php](https://github.com/bearsunday/BEAR.Package/blob/1.x/docs/demo-app/src/Resource/App/User.php)

```php
namespace MyVendor\MyApp\Resource\App;

use BEAR\Package\Annotation\Etag;
use BEAR\RepositoryModule\Annotation\QueryRepository;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

/**
 * @QueryRepository
 * @Etag
 */
class User extends ResourceObject
{
    /**
     * @Link(rel="profile", href="/profile{?id}")
     * @Embed(rel="website", src="/website{?id}")
     * @Embed(rel="contact", src="/contact{?id}")
     */
    public function onGet($id)
    {
        $this['id'] = $id;
        $this['name'] = 'Akihito Koriyama';

        return $this;
    }
}
```
## Console

### web access (page resource)

    $ cd docs/demo-app/bootstrap
    $ php web.php options /user

    200 OK
    allow: get


### api access (api resource)

    $ cd docs/demo-app/bootstrap
    $ php api.php get '/user?id=koriym'

    200 OK
    content-type: application/hal+json
    Etag: 2037294968
    Last-Modified: Tue, 14 Apr 2015 13:29:05 GMT

    {
        "id": "koriym",
        "name": "Akihito Koriyama",
        "_embedded": {
            "website": {
                "url": "http:://example.org/koriym",
                "id": "koriym",
                "_links": {
                    "self": {
                        "href": "/website?id=koriym"
                    }
                }
            },
            "contact": {
                "contact": [
                    {
                        "id": "1",
                        "name": "Athos"
                    },
                    {
                        "id": "2",
                        "name": "Porthos"
                    },
                    {
                        "id": "3",
                        "name": "Aramis"
                    }
                ],
                "_links": {
                    "self": {
                        "href": "/contact?id=koriym"
                    }
                }
            }
        },
        "_links": {
            "self": {
                "href": "/user/koriym"
            },
            "profile": {
                "href": "/profile/koriym"
            }
        }
    }

## Application Context

### built-in module

 * `api` for API application
 * `cli` for console application
 * `hal` for Hypertext Application Language application
 * `prod` for production

To run application, Include application invoke script with contexts value as `$context`.

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
class PdoProvider implements ProviderInterface
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

## Extra modules

 * [Ray.Di Modules](https://github.com/Ray-Di)

## Build status

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Sunday.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Sunday?branch=1.x)
**BEAR.Sunday** - [Resource Oriented Framework](https://github.com/bearsunday/BEAR.Sunday)

 [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/) [![Code Coverage](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/) [![Build Status](https://secure.travis-ci.org/ray-di/Ray.Aop.png?b=2.x)](http://travis-ci.org/ray-di/Ray.Aop) **Ray.Aop** - [Aspect Oriented Framework](https://github.com/ray-di/Ray.Aop)

 [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/) [![Code Coverage](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/) [![Build Status](https://secure.travis-ci.org/ray-di/Ray.Di.png?b=2.x)](http://travis-ci.org/ray-di/Ray.Di) **Ray.Di** - [Dependency Injection Framework](https://github.com/ray-di/Ray.Di)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/quality-score.png?b=develop-2)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=develop-2) [![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/coverage.png?b=develop-2)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=develop-2) [![Build Status](https://travis-ci.org/bearsunday/BEAR.Resource.svg?branch=develop-2)](https://travis-ci.org/bearsunday/BEAR.Resource)
**BEAR.Resource** - [Hypermedia Framework](https://github.com/bearsunday/BEAR.Resource)

## Requirements

 * PHP 5.5+
