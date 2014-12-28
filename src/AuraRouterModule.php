<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use Aura\Router\Router;
use Aura\Web\WebFactory;
use BEAR\Package\Provide\Router\AuraRouterProvider;
use BEAR\Package\Provide\Router\RouterCollectionProvider;
use BEAR\Sunday\Exception\LogicException;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class AuraRouterModule extends AbstractModule
{
    protected function configure()
    {
        if (! class_exists(Router::class) || ! class_exists(WebFactory::class)) {
            throw new LogicException('"aura/router" and "aura/web" package are required.');
        }
        $this->bind(RouterInterface::class)->annotatedWith('primary_router')->toProvider(AuraRouterProvider::class);
        $this->bind(RouterInterface::class)->toProvider(RouterCollectionProvider::class)->in(Scope::SINGLETON);
    }
}
