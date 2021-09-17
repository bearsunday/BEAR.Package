<?php

declare(strict_types=1);

use BEAR\Package\Bootstrap;
use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;

/* @var \Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__, 2) . '/vendor/autoload.php';
$loader->addPsr4('MyVendor\\MyProject\\', dirname(__DIR__) . '/src');
$page = Injector::getInstance('MyVendor\MyProject', 'prod-app', dirname(__DIR__, 2))->getInstance(ResourceInterface::class)->get->uri('page://self/api/user')(['id' => 1]);

echo $page->code . PHP_EOL;
echo (string) $page;

//200
//{
//    "id": 1,
//    "name": "Koriym",
//    "_embedded": {
//    "website": {
//        "url": "http:://example.org/1",
//            "_links": {
//            "self": {
//                "href": "/api/website?id=1"
//                }
//            }
//        },
//        "contact": {
//        "contact": [
//                {
//                    "id": "1",
//                    "name": "Athos"
//                },
//                {
//                    "id": "2",
//                    "name": "Porthos"
//                },
//                {
//                    "id": "3",
//                    "name": "Aramis"
//                }
//            ],
//            "_links": {
//            "self": {
//                "href": "/api/contact?id=1"
//                }
//            }
//        }
//    },
//    "_links": {
//    "self": {
//        "href": "/api/user?id=1"
//        },
//        "profile": {
//        "href": "/api/profile?id=1"
//        }
//    }
//}
