<?php
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

ob_start();

class FireTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Fire
     */
    private $fire;

    /**
     * @var Mock
     */
    private $ro;

    public static function setUpBeforeClass()
    {
        ob_start(); // <-- very important!
    }

    public static function tearDownAfterClass()
    {
        ob_end_clean();
    }

    protected function setUp()
    {
        parent::setUp();
        $_SERVER['HTTP_USER_AGENT'] = 'User-Agent:  FirePHP/0.7.1';
        $this->fire = new Fire(\FirePHP::getInstance(true));
        $this->request = require $GLOBALS['_BEAR_TEST_DIR'] . '/scripts/instance/request.php';
        $this->request->set(new Mock, 'nop://mock', 'get', []);
        $this->ro = new Mock;
        $this->request->set(new Mock, 'nop://mock', 'get', []);
        $this->ro->onGet(1, 2);
        $this->ro->headers = [
            'header1' => 1,
            'header2' => 2
        ];
    }

    protected function tearDown()
    {
        parent::tearDown();
        header_remove(); // <-- VERY important.
        unset($_SERVER['HTTP_USER_AGENT']);
    }

    public function testWrite()
    {
        ob_start();
        $this->fire->write($this->request, $this->ro);
        $headersList = print_r(xdebug_get_headers(), true);
        $this->assertContains('"Type":"TABLE","Label":"headers","File"', $headersList);
        ob_end_clean();
    }

    public function testWriteBodyCanBeTable()
    {
        $this->ro->body = [
            ['name' => 'bear'],
            ['name' => 'koriym']
        ];
        $this->fire->write($this->request, $this->ro);
        $headersList = print_r(xdebug_get_headers(), true);
        $this->assertContains('[["name"],["bear"],["koriym"]]', $headersList);
    }

    public function testFireLinks()
    {
        $this->ro->links = [
            'rel1' => 'page://self/rel1',
            'rel2' => 'page://self/rel2'
        ];
        $this->fire->write($this->request, $this->ro);
        $headersList = print_r(xdebug_get_headers(), true);
        $this->assertContains('["rel1","page:\\/\\/self\\/rel1"]', $headersList);
    }

    public function testFireVariousBody()
    {
        $ro1 = clone $this->ro;
        $this->ro->body = [
            'object' => new \stdClass,
            'ro' => $ro1,
            'request' => $this->request
        ];
        $this->fire->write($this->request, $this->ro);
        $headersList = print_r(xdebug_get_headers(), true);
        $this->assertContains('"request":"(Request) nop:\\/\\/mock"', $headersList);
    }

    public function testFireVariousBodyIsString()
    {
        $this->ro->body = 'body_is_string';
        $this->fire->write($this->request, $this->ro);
        $headersList = print_r(xdebug_get_headers(), true);
        $this->assertContains('"body_is_string"', $headersList);
    }
}
