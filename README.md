# BEAR.Package

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Package.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Package)

BEAR.Package is a [BEAR.Sunday](https://github.com/bearsunday/BEAR.Sunday) resource oriented framework implementation package.

## Run demo app

```
composer create-project -n bear/package bear.package ~1.0
cd bear.package
```

A resource can then be accessed from the console. 
```
php docs/demo-app/bootstrap/web.php get /
```
```
200 OK
content-type: application/hal+json

{
    "greeting": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/"
        }
    }
}
```
Fire up the built-in php web server.
```
php -S 127.0.0.1:8080 -t docs/demo-app/var/www
```
You can then open a browser at `http://127.0.0.1:8080` to see the json output.

## Documentation

Documentation is available at http://bearsunday.github.io/.
