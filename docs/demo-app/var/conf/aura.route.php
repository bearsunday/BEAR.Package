<?php
/**
 * Aura.Router route file
 *
 * @see https://github.com/auraphp/Aura.Router
 */

/** @var $router \Aura\Router\RouteCollection */
$router->add('/user', '/user/{id}')->addValues(['path' => '/user']);
$router->add('/profile', '/profile/{id}')->addValues(['path' => '/profile']);
