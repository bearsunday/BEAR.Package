<?php

namespace BEAR\Package\Provide\ConsoleOutput;

use BEAR\Package\Mock\ResourceObject\MockResource;

class ConsoleOutputTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConsoleOutput
     */
    protected $consoleOutput;

    protected function setUp()
    {
        $this->consoleOutput = (new ConsoleOutput)->disableOutput();
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Provide\ConsoleOutput\ConsoleOutput', $this->consoleOutput);
    }

    public function testOutput()
    {
        $output = $this->consoleOutput->send(new MockResource, 'Ok');
        $this->assertContains('Ok', $output);
    }

    public function testOutputBodyContainsRequest()
    {
        $mock = new MockResource;
        $request = require $_ENV['TEST_DIR'] . '/scripts/instance/request.php';
        /** @var $request \BEAR\Resource\Request */
        $request->set(new MockResource, 'nop://mock', 'get', []);
        $mock->body['req1'] = $request;
        $mock->body['array1'] = [1, 2, 3];
        $output = $this->consoleOutput->send($mock, 'Ok');
        $this->assertContains('Ok', $output);
    }

    public function testOutputBodyIsString()
    {
        $mock = new MockResource;
        $mock->body = "hello";
        $output = $this->consoleOutput->send($mock, 'Ok');
        $this->assertContains(ConsoleOutput::LABEL . '[BODY]' . ConsoleOutput::CLOSE . PHP_EOL . 'hello', $output);
    }

    public function testOutputHasView()
    {
        $mock = new MockResource;
        $mock->view = "my view";
        $output = $this->consoleOutput->send($mock, 'Ok');
        $this->assertContains(ConsoleOutput::LABEL . '[VIEW]' . ConsoleOutput::CLOSE . PHP_EOL . 'my view', $output);
    }
    public function testOutputBodyIsObject()
    {
        $mock = new MockResource;
        $mock->body = new \stdClass;
        $output = $this->consoleOutput->send($mock, 'Ok');
        $this->assertContains(ConsoleOutput::LABEL . '[BODY]' . ConsoleOutput::CLOSE . PHP_EOL . '*object', $output);
    }
    public function testOutputBodyIsObjectInArray()
    {
        ob_start();
        $mock = new MockResource;
        $mock->body = ['key' => new \stdClass];
        $output = $this->consoleOutput->send($mock, 'Ok');
        $ob = ob_get_clean();
        $this->assertContains(ConsoleOutput::LABEL . '[BODY]' . ConsoleOutput::CLOSE . PHP_EOL . ConsoleOutput::LABEL1 . 'key' . ConsoleOutput::CLOSE . ' => stdClass(', $output);
    }
}
