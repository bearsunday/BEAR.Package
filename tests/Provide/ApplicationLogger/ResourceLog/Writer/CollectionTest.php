<?php
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Null;

require_once __DIR__ . '/Mock.php';

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    private $null;

    /**
     * @var Void
     */
    protected $logger;

    protected function setUp()
    {
        $this->null = new Null;
        $this->null1 = new Null;
        $this->logger = new Collection([$this->null, $this->null]);
        $this->request = require $_ENV['TEST_DIR'] . '/scripts/instance/request.php';
    }

    /**
     * @covers BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Collection::write
     */
    public function testLog()
    {
        $ro = new Mock;
        $this->request->set(new Mock, 'nop://mock', 'get', []);
        $ro->onGet(1, 2);
        $this->logger->write($this->request, $ro);
        $log0 = $this->null->logs[0];
        $this->assertSame($this->request, $log0[0]);
        $this->assertSame($ro, $log0[1]);
    }
}
