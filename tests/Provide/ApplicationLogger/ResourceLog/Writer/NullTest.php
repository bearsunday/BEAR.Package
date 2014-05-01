<?php
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Null;

require_once __DIR__ . '/Mock.php';

class NullTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Void
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Null;
        $this->request = require $_ENV['TEST_DIR'] . '/scripts/instance/request.php';
    }

    protected function tearDown()
    {
    }

    /**
     * @covers BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Null::write
     */
    public function testLog()
    {
        $ro = new Mock;
        $this->request->set(new Mock, 'nop://mock', 'get', []);
        $ro->onGet(1, 2);
        $this->object->write($this->request, $ro);
        $log0 = $this->object->logs[0];
        $this->assertSame($this->request, $log0[0]);
        $this->assertSame($ro, $log0[1]);
    }
}
