<?php
namespace BEAR\Package\Extra;

use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Logger;
use BEAR\Package\Provide\ApplicationLogger\ResourceLogOutput;
use BEAR\Resource\Logger as ResourceLogger;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Di\Injector;
use Demo\Sandbox\Module\AppModule;
use BEAR\Resource\LogWriterInterface;
use Ray\Di\Di\Inject;

class Service implements AppInterface
{
    public $writer1;
    public $writer2;

    /**
     * @Inject
     */
    public function setLogWrite1(LogWriterInterface $writer)
    {
        $this->writer1 = $writer;
    }

    /**
     * @Inject
     */
    public function setLogWrite2(LogWriterInterface $writer)
    {
        $this->writer2 = $writer;
    }
}

class Service2 implements AppInterface
{
    public $writer1;
    public $writer2;

    /**
     * @Inject
     */
    public function setLogWrite1(LogWriterInterface $writer)
    {
        $this->writer1 = $writer;
    }

    /**
     * @Inject
     */
    public function setLogWrite2(LogWriterInterface $writer)
    {
        $this->writer2 = $writer;
    }
}

class SingletonModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Injector
     */
    protected $injector;

    protected function setUp()
    {
        static $injector;

        if (! $injector) {
            $injector = Injector::create([new AppModule]);
        }
        $this->injector = $injector;
        parent::setUp();
    }

    public function testApplicationLoggerSingleton()
    {
        $logger1 = $this->injector->getInstance('BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface');
        $logger2 = $this->injector->getInstance('BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface');
        $this->assertSame(spl_object_hash($logger1), spl_object_hash($logger2));
    }

    public function testLogWriterSingleton()
    {
        $writer1 = $this->injector->getInstance('BEAR\Resource\LogWriterInterface');
        $writer2 = $this->injector->getInstance('BEAR\Resource\LogWriterInterface');
        $this->assertSame(spl_object_hash($writer1), spl_object_hash($writer2));
    }

    public function testLogWriterInServiceSingleton()
    {
        $service = $this->injector->getInstance(__NAMESPACE__ . '\Service');
        $this->assertSame(spl_object_hash($service->writer1), spl_object_hash($service->writer2));
    }

    public function testFromAnotherServiceSingleton()
    {
        $service = $this->injector->getInstance(__NAMESPACE__ . '\Service');
        $service2 = $this->injector->getInstance(__NAMESPACE__ . '\Service2');
        $this->assertSame(spl_object_hash($service->writer1), spl_object_hash($service2->writer2));
    }

}
