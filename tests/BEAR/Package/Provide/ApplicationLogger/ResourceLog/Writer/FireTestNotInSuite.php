<?php
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

class FireTestNotInSuite extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Fire
     */
    private $fire;

    /**
     * @var Mock
     */
    private $ro;

    protected function setUp()
    {
        xhprof_disable();
        ob_start();
        header_remove();
        parent::setUp();
        $_SERVER['HTTP_USER_AGENT'] = 'User-Agent:  FirePHP/0.7.1';
        $this->fire = new Fire(\FirePHP::getInstance(true));
        $this->request = require $_ENV['TEST_DIR'] . '/scripts/instance/request.php';
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
        unset($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testWrite()
    {
        $this->fire->write($this->request, $this->ro);
        $headersList = print_r(xdebug_get_headers(), true);
        var_dump($headersList);
        $this->assertContains('"Type":"TABLE","Label":"headers","File"', $headersList);
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
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

    /**
     * @runInSeparateProcess
     */
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

    /**
     * @runInSeparateProcess
     */
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

    /**
     * @runInSeparateProcess
     */
    public function testFireVariousBodyIsString()
    {
        $this->ro->body = 'body_is_string';
        $this->fire->write($this->request, $this->ro);
        $headersList = print_r(xdebug_get_headers(), true);
        $this->assertContains('"body_is_string"', $headersList);
    }
}
