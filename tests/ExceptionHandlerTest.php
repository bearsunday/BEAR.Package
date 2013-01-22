<?php
namespace BEAR\Package\Tests;

use BEAR\Package\Output\Console;
use BEAR\Package\Debug\ExceptionHandle\ExceptionHandler;
use BEAR\Package\Web\SymfonyResponse;

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->exceptionHandler = new ExceptionHandler(
            'dummy.tpl',
            new SymfonyResponse(new Console)
        );
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Debug\ExceptionHandle\ExceptionHandler', $this->exceptionHandler);
    }

}
