BEAR.Package
=============================

[![Build Status](https://secure.travis-ci.org/koriym/BEAR.Package.png?branch=master)](http://travis-ci.org/koriym/BEAR.Package)

Introduction
------------
This is a sandbox application using the [https://github.com/koriym/BEAR.Sunday](BEAR.Sunday) resource oriented framework.

Installation
------------

Here's how to install sandbox application using BEAR.Sunday:

    git clone git://github.com/koriym/BEAR.Package.git
    cd BEAR.Package
    wget http://getcomposer.org/composer.phar
    sudo php composer.phar install
    php scripts/check_env.php
    chmod -R 777 apps/Sandbox/data

buil-in web server for development
------------------

    cd apps/Sandbox/public
    php -S localhost:8088 web.php
    php -S localhost:8089 api.php

Console
-------

    php web.php get /
    php api.php get app://self/greetings?lang=ja
    php api.php get app://self/greetings?lang=en
    php api.php get page://self/index

Virtual Host for Production
------------
Set up a virtual host to point to the public/ directory of the application.
