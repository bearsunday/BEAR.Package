<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
require dirname(__DIR__) . '/vendor/autoload.php';

$page = (new \BEAR\Package\Bootstrap())
    ->getApp('MyVendor\MyProject', 'hal-app')
    ->resource
    ->get
    ->uri('page://self/api/user')(['id' => 1]);

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
