<?php
namespace BEAR\Package\tests\Module\ExceptionHandle;

use BEAR\Package\Dev\Debug\ExceptionHandle\Screen;
use Aura\Di\Exception;

class ScreenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Screen
     */
    private $screen;

    protected function setUp()
    {
        parent::setUp();
        $this->screen = new Screen;
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Dev\Debug\ExceptionHandle\Screen', $this->screen);
    }

    public function getTraceAsJsString()
    {
        $trace = new \Exception;
        $string = $this->screen->getTraceAsJsString($trace->getTrace());
        error_log($string);
        $this->assertInternalType('string', $string);
    }
}
