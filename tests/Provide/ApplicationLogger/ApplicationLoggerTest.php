<?php
namespace BEAR\Package\Provide\ApplicationLogger;

use BEAR\Package\Provide\ApplicationLogger\ResourceLogOutput;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Resource\Logger as ResourceLogger;
use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Logger;

class App implements AppInterface
{
}

class ApplicationLoggerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Logger
     */
    protected $resourceLogger;

    /**
     * @var ApplicationLogger
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new ApplicationLogger(new ResourceLogger);
    }

    public function testRegister()
    {
        $result = $this->object->write();
        $this->assertSame(null, $result);

    }
}
