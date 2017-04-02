BEAR.Package 0.x (ARCHIVED)
=============================

Requirement
-----------

*  PHP 5.4.0 - 7.1.0

Introduction
------------

BEAR.Package is a [BEAR.Sunday](https://github.com/koriym/BEAR.Sunday) resource oriented framework implementation package.

Run Sandbox demo app
--------------------

    $ composer create-project bear/package BEAR0 ~0.13
    $ cd BEAR0
    $ php apps/Demo.Sandbox/bin/install_db.sh
    $ bin/bear.server apps/Demo.Sandbox

You can then open a browser and go to `http://0.0.0.0:8080` to see the Sabdbox demo output.

Application Dev Tool
--------------------

To see application dev tool page, go to `http://0.0.0.0:8080/dev/`

System Admin Tool
------------------
To see system admin page

    $ php -S 0.0.0.0:8090 -t {$PACKAGE_PATH}/var/www/admin
    
go to `http://0.0.0.0:8090/`

Virtual Host for Production
------------

Set up a virtual host to point to the `{$PACKAGE_PATH}/apps/Demo.Sandbox/var/www/` directory of the application.

Console
-------

### web access

```bash
$ cd {$PACKAGE_PATH}/apps/Demo.Sandbox/bootstrap/contexts
$ php dev.php get /

200 OK
x-interceptors: ["{\"onGet\":[\"Sandbox\\\\Interceptor\\\\Checker\"]}"]
x-execution-time: [0.068794012069702]
x-profile-id: ["523ee4ba886de"]
cache-control: ["no-cache"]
date: ["Sun, 22 Sep 2013 12:38:18 GMT"]
[BODY]
greeting: Hello World
version: array (
  'php' => '5.4.16',
  'BEAR' => 'dev-develop',
  'extensions' => 
  array (
    'apc' => '3.1.13',
  ),
)
performance: app://self/performance

[VIEW]
<!DOCTYPE html>
<html lang="en">
...
```

### api access

```bash
$ php api.php get page://self/index
$ php api.php get 'app://self/first/greeting?name=World'

200 OK
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Sun, 22 Sep 2013 12:42:48 GMT"]
[BODY]
Hello, World


$ php api.php get app://self/blog/posts
```

Make your own application
-------------------------

    $ cd {$PACKAGE_PATH}/apps

### install

    $ composer create-project bear/skeleton {Vendor.AppName}
    $ composer create-project bear/skeleton {Vendor.AppName} dev-develop

### first run

    $ cd {$PACKAGE_PATH}

    // Console
    $ php apps/{Vendor.AppName}/bootstrap/contexts/dev.php get /

    // Web
    $ bin/bear.server apps/{Vendor.AppName}

### test

    $ phpunit

### application first

    $ composer install
