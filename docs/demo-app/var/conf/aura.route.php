<?php
/**
 * Aura.Router route file
 *
 * @see https://github.com/auraphp/Aura.Router
 */

/** @var $router \Aura\Router\RouteCollection */

$router->addGet('user', '/user/{id}')
    ->addValues(['path' => '/user']);
