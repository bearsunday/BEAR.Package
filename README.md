BEAR.Package
=============================

[![Build Status](https://secure.travis-ci.org/koriym/BEAR.Package.png?branch=master)](http://travis-ci.org/koriym/BEAR.Package)

Introduction
------------
This is a sandbox application using the [https://github.com/koriym/BEAR.Sunday](BEAR.Sunday) resource oriented framework.

Installation
------------

Here's how to install sandbox application using BEAR.Sunday:

    curl -s https://getcomposer.org/installer | php
    php composer.phar create-project -s dev bear/package ./bear

More information is availavle at [wiki:install](http://code.google.com/p/bearsunday/wiki/install).

built-in web server for development
------------------

    cd apps/Sandbox/public
    php -S localhost:8088 web.php
    php -S localhost:8089 api.php

Virtual Host for Production
------------
Set up a virtual host to point to the public/ directory of the application.

Console
-------

    $ php web.php get /index
    $ php api.php get page://self/index
    $ php api.php get 'app://self/first/greeting?name=World'
    $ php api.php get app://self/blog/posts

### Try HATEOAS application (See [how to GET a cup of coffee](http://www.infoq.com/articles/webber-rest-workflow))

    $ php api.php options 'app://self/restbucks/order'

    200 OK
    allow: ["get","post","put","delete"]
    param-get: id
    param-post: drink
    param-put: id,(addition),(status)
    param-delete: id
    content-type: application/hal+json; charset=UTF-8
    [BODY]
    {
        "_links": {
            "self": {
                "href": "app://self/restbucks/order"
            }
        }
    }

    $ php api.php post 'app://self/restbucks/order?drink=latte'
    201 Created
    content-type: application/hal+json; charset=UTF-8
    [BODY]
    {
        "drink": "latte",
        "cost": 2.5,
        "id": "4028",
        "_links": {
            "self": {
                "href": "app://self/restbucks/order?drink=latte"
            },
            "payment": {
                "href": "app://self/restbucks/payment?id=4028"
            }
        }
    }

    $ php api.php put 'app://self/restbucks/order?id=4208&addition=shot'
    100 Continue
    content-type: application/hal+json; charset=UTF-8
    [BODY]
    {
        "drink": "latte",
        "cost": 2.5,
        "id": "5534",
        "_links": {
            "self": {
                "href": "app://self/restbucks/order?id=5534&addition=shot"
            },
            "payment": {
                "href": "app://self/restbucks/payment?id=5534"
            }
        },
        "addition": "shot"
    }

    $ php api.php options 'app://self/restbucks/payment'
    200 OK
    allow: ["get","put"]
    param-get: id
    param-put: id,card_no,expires,name,amount
    content-type: application/hal+json; charset=UTF-8
    [BODY]
    {
        "_links": {
            "self": {
                "href": "app://self/restbucks/payment"
            }
        }
    }

    $ php api.php put 'app://self/restbucks/payment?id=5534&card_no=0000123408010908&expires=021014&name=BEAR%20SUNDAY&amount=1'
    201 Created
    content-type: application/hal+json; charset=UTF-8
    [BODY]
    {
        "card_no": "0000123408010908",
        "expires": "021014",
        "name": "BEAR SUNDAY",
        "amount": "1",
        "_links": {
            "self": {
                "href": "app://self/restbucks/payment?id=5534&card_no=0000123408010908&expires=021014&name=BEAR%20SUNDAY&amount=1"
            }
        }
    }

    $php api.php get 'app://self/restbucks/orders'
    200 OK
    content-type: application/hal+json; charset=UTF-8
    [BODY]
    {
        "order": [
            {
                "drink": "latte",
                "cost": 2.5,
                "id": "5534",
                "_links": {
                    "self": {
                        "href": "app://self/restbucks/order?id=5534"
                    },
                    "payment": {
                        "href": "app://self/restbucks/payment?id=5534"
                    },
                    "edit": {
                        "href": "app://self/restbucks/order?id=5534"
                    }
                },
                "addition": "shot"
            }
        ],
        "_links": {
            "self": {
                "href": "app://self/restbucks/orders"
            }
        }
    }

    ... be continued at
	
    web:     /restbucks/
    console: php api.php post 'page://self/restbucks/index?drink=latte'
