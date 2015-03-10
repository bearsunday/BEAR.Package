<?php

\BEAR\Package\Provide\Router\AuraRouterModuleTest::$routerClass = $router;

/* @var $router \Aura\Router\RouteCollection */
$router->add('/user', '/user/{id}')->addValues(['path' => '/user']);
$router->add('/friend', '/friend/{id}')->addValues(['path' => '/friend']);
