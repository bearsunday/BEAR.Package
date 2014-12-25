<?php
/** @var $router \Aura\Router\RouteCollection */
$nameRegex = '[-a-zA-Z]+';

$router->add('hello', '/hello/{name}')
    ->addValues(['path' => '/index'])
    ->addTokens(['name' => $nameRegex]);
