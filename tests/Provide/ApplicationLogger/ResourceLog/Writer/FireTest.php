<?php
namespace BEAR\Package\tests\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Fire;

require_once __DIR__ . '/Mock.php';

class FireTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Void
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Fire(\FirePHP::getInstance(true));
        $this->request = require _BEAR_TEST_DIR . '/scripts/instance/request.php';
    }

    /**
     * ob_start() does'nt work
     * this test does not ensure whole functionality.
     *
     * @covers BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Fire::write
     *
     * @expectedException Exception
     */
    public function testLog()
    {
        $ro = new Mock;
        $this->request->set(new Mock, 'nop://mock', 'get', []);
        $ro->onGet(1, 2);

        ob_start();
        $this->object->write($this->request, $ro);
        $headers_list = headers_list();
        header_remove();
    }
}
