<?php

namespace BEAR\Package\Provide\Application;

use Any\Serializer\Serializer;
use Aura\Router\DefinitionFactory;
use Aura\Router\Map;
use Aura\Router\RouteFactory;
use Aura\Signal\HandlerFactory;
use Aura\Signal\Manager;
use Aura\Signal\ResultCollection;
use Aura\Signal\ResultFactory;
use BEAR\Package\Provide\ConsoleOutput\ConsoleOutput;
use BEAR\Package\Provide\Router\Adapter\WebRouter;
use BEAR\Package\Provide\Router\Router;
use BEAR\Package\Provide\WebResponse\HttpFoundation as WebResponse;
use BEAR\Resource\Anchor;
use BEAR\Resource\Factory;
use BEAR\Resource\Invoker;
use BEAR\Resource\Linker;

use BEAR\Resource\Logger;
use BEAR\Resource\NamedParameter;
use BEAR\Resource\Param;
use BEAR\Resource\Request;
use BEAR\Resource\Resource;
use BEAR\Resource\SchemeCollection;
use BEAR\Resource\SignalParameter;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use PHPParser_BuilderFactory;
use PHPParser_Lexer;
use PHPParser_Parser;
use PHPParser_PrettyPrinter_Default;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use Ray\Di\Annotation;
use Ray\Di\Config;
use Ray\Di\Container;
use Ray\Di\Definition;
use Ray\Di\EmptyModule;
use Ray\Di\Forge;
use Ray\Di\Injector;

use Ray\Di\Logger as DiLogger;

class MyApp extends AbstractApp
{
}

class AbstractApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MyApp
     */
    protected $app;

    protected function setUp()
    {
        $invoker = new Invoker(
            new Linker(
                new AnnotationReader,
                new ArrayCache
            ),
            new NamedParameter(
                new SignalParameter(
                    new Manager(
                        new HandlerFactory,
                        new ResultFactory,
                        new ResultCollection
                    ),
                    new Param
                )
            ),
            new Logger
        );
        $this->app = new MyApp(
            new Resource(
                new Factory(new SchemeCollection),
                $invoker,
                new Request($invoker),
                new Anchor(
                    new AnnotationReader,
                    new Request($invoker)
                )
            ),
            new WebResponse(
                new ConsoleOutput
            ),
            new Router(new WebRouter)
        );
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Provide\Application\AbstractApp', $this->app);
    }
}
