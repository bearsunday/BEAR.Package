<?php
namespace BEAR\Package\tests\Module\ExceptionHandle;

use BEAR\Package\Provide\ConsoleOutput\ConsoleOutput;
use BEAR\Package\Debug\ExceptionHandle\ExceptionHandler;
use BEAR\Package\Provide\WebResponse\HttpFoundation;

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExceptionHandler
     */
    private $exceptionHandler;

    protected function setUp()
    {
        parent::setUp();
        $this->exceptionHandler = new ExceptionHandler('dummy.tpl', new HttpFoundation(new ConsoleOutput));
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Debug\ExceptionHandle\ExceptionHandler', $this->exceptionHandler);
    }
}
