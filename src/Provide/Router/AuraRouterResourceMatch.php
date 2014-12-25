<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Aura\Router\RouteCollection;

class AuraRouterResourceMatch extends RouteCollection
{
    /**
     *
     * Callable for BEAR resource routes.
     *
     * @param RouteCollection $router A RouteCollection
     */
    protected function resourceCallable(RouteCollection $router)
    {
        // add 'id' and 'format' if not already defined
        $tokens = array();
        if (! isset($router->tokens['id'])) {
            $tokens['id'] = '\d+';
        }
        if (! isset($router->tokens['format'])) {
            $tokens['format'] = '(\.[^/]+)?';
        }
        if ($tokens) {
            $router->addTokens($tokens);
        }

        // add the routes
        $router->addGet('get', '{format}');
        $router->addGet('get', '/{id}{format}');
        $router->addGet('get', '/{id}/edit{format}');
        $router->addGet('post', '/add');
        $router->addDelete('delete', '/{id}');
        $router->addPost('post', '');
        $router->addPatch('patch', '/{id}');
        $router->addPut('put', '/{id}');
        $router->addOptions('options', '');
    }
}
