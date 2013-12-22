BEAR.Package
=============================

 * master  [![Latest Stable Version](https://poser.pugx.org/bear/package/v/stable.png)](https://packagist.org/packages/bear/package)[![Build Status](https://secure.travis-ci.org/koriym/BEAR.Package.png?branch=master)] (http://travis-ci.org/koriym/BEAR.Package)
 * develop [![Latest Unstable Version](https://poser.pugx.org/bear/package/v/unstable.png)](https://packagist.org/packages/bear/package)[![Build Status](https://secure.travis-ci.org/koriym/BEAR.Package.png?branch=develop)](http://travis-ci.org/koriym/BEAR.Package)

Introduction
------------
BEAR.Package is a [BEAR.Sunday](https://github.com/koriym/BEAR.Sunday) resource oriented framework implementation package.
Installation
------------

    $ composer create-project bear/package {$PROJECT_PATH}

built-in web server for development
------------------

for Sandbox web page

    $ php bin/server.php apps/Demo/Sandbox
    $ php bin/server.php --context=api --port=8081 apps/Demo/Sandbox

or

    $ php -S 0.0.0.0:8080 -t apps/Demo/Sandbox/var/www/ apps/Demo/Sandbox/bootstrap/contexts/dev.php

You can then open a browser and go to `http://0.0.0.0:8080` to see the "Hello World" demo output. To see application dev tool page, go to `http://0.0.0.0:8088/dev/`

for system admin page

    $ php -S 0.0.0.0:8090 -t {$PROJECT_PATH}/var/www/admin

Virtual Host for Production
------------
Set up a virtual host to point to the `{$PROJECT_PATH}/apps/Demo/Sandbox/var/www/` directory of the application.

Console
-------

### web access
```bash

$ cd {$PROJECT_PATH}/apps/Demo/Sandbox
$ php bin/web.php get /
    
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
$ php bin/api.php get page://self/index
$ php bin/api.php get 'app://self/first/greeting?name=World'

200 OK
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Sun, 22 Sep 2013 12:42:48 GMT"]
[BODY]
Hello, World


$ php bin/api.php get app://self/blog/posts
```

Make your own application
----------------------------------
### install

    $ php bin/new_app.php {NewAppName}

### test

    $ cd apps/{NewAppName}
    $ phpunit

### run
    $ cd var/www
    // Console
    $ php dev.php get /
    // Web
    $ php -S 0.0.0.0:8080 dev.php
