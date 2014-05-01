<?php
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

require_once __DIR__ . '/Mock.php';

class Zf2LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Zf2Log
     */
    private $zf2Logger;

    /**
     * @var Mock
     */
    private $ro;

    protected function setUp()
    {
        parent::setUp();
        $this->zf2Logger = new \BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Zf2Log(new Zf2LogProvider(__DIR__));
        $this->request = require $_ENV['TEST_DIR'] . '/scripts/instance/request.php';
        $this->ro = new Mock;
        $this->request->set(new Mock, 'nop://mock', 'get', []);
        $this->ro->onGet(1, 2);
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($_SERVER['PATH_INFO']);
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Zf2Log', $this->zf2Logger);
    }

    public function testWrite()
    {
        $result = $this->zf2Logger->write($this->request, $this->ro);
        $this->assertSame(null, $result);
    }

    public function testWritePathInfo()
    {
        $_SERVER['PATH_INFO'] = '/test/a';
        $result = $this->zf2Logger->write($this->request, $this->ro);
        $this->assertSame(null, $result);
    }
}
