<?php
namespace BEAR\Package\Provide\ApplicationLogger\Module;

use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Logger;
use BEAR\Package\Provide\ApplicationLogger\ResourceLogOutput;
use BEAR\Resource\Logger as ResourceLogger;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Di\Injector;
use Sandbox\Module\AppModule;

class App implements AppInterface
{
}

class ApplicationLoggerModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Injector
     */
    protected $injector;

    protected function setUp()
    {
        $this->injector = Injector::create([new AppModule]);
    }

    public function testApplicationLoggerIsSingletonInstance()
    {
        $logger1 = $this->injector->getInstance('BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface');
        $logger2 = $this->injector->getInstance('BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface');
        $this->assertSame(spl_object_hash($logger1), spl_object_hash($logger2));
    }
}
