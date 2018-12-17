#!/usr/bin/env bash

function clearCacheAndLogs() {
    sudo /bin/rm -rf var/tmp/*
    [ "$?" != "0" ] && exit 1
    sudo /bin/chmod -R 777 var/tmp
    [ "$?" != "0" ] && exit 1

    sudo /bin/rm -rf var/log/*
    [ "$?" != "0" ] && exit 1
    sudo /bin/chmod -R 777 var/log
    [ "$?" != "0" ] && exit 1
}

function init() {
    sudo /bin/rm -rf vendor/
    clearCacheAndLogs

    composer install --no-dev
    [ "$?" != "0" ] && exit 1

    clearCacheAndLogs
     ./vendor/bin/bear.compile 'MyVendor\MyProject' prod-app ./
     ./vendor/bin/bear.compile 'MyVendor\MyProject' prod-app ./

    return 0
}
