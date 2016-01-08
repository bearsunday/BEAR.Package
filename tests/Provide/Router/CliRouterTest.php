<?php

namespace BEAR\Package\Provide\Router;

use Aura\Cli\CliFactory;

class CliRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CliRouter
     */
    private $router;

    private $stdInFile;

    public function argvProvider()
    {
        return [
            ['get', 'page://self/?name=bear', ['name' => 'bear'], [], ''],
            ['post', 'page://self/?name=bear', [], ['name' => 'bear'], ''],
            ['put', 'page://self/?name=bear', [], [], 'name=bear'],
            ['patch', 'page://self/?name=bear', [], [], 'name=bear'],
            ['delete', 'page://self/?name=bear', [], [], 'name=bear']
        ];
    }

    public function setUp()
    {
        $stdOut = $_ENV['TEST_DIR'] . '/stdout.log';
        $this->stdInFile = $_ENV['TEST_DIR'] . '/stdin.text';
        $stdIo = (new CliFactory())->newStdio('php://stdin', $stdOut);
        $httpMethodParams = new HttpMethodParams;
        $httpMethodParams->setStdIn($this->stdInFile);
        $this->router = new CliRouter(new WebRouter('page://self', $httpMethodParams), new \InvalidArgumentException, $stdIo);
        $this->router->setStdIn($this->stdInFile);
    }

    public function tearDown()
    {
        @unlink(dirname(dirname(__DIR__)) . '/stdin.text');
        @unlink(dirname(dirname(__DIR__)) . '/stdout.log');
    }

    /**
     * @dataProvider argvProvider
     */
    public function testMatch($argv2, $argv3, array $get, array $post, $stdin)
    {
        $globals = [
            'argv' => [
                'php',
                $argv2,
                $argv3
            ],
            'argc' => 3,
            '_GET' => $get,
            '_POST' => $post,
        ];
        if ($stdin) {
            file_put_contents($this->stdInFile, $stdin);
        }
        $request = $this->router->match($globals, []);
        $this->assertSame($argv2, $request->method);
        $this->assertSame('page://self/', $request->path);
        $this->assertSame(['name' => 'bear'], $request->query);
    }

    public function testOption()
    {
        $globals = [
            'argv' => [
                'php',
                'options',
                'page://self/'
            ],
            'argc' => 3,
            '_GET' => [],
            '_POST' => [],
        ];
        $request = $this->router->match($globals, []);
        $this->assertSame('options', $request->method);
        $this->assertSame('page://self/', $request->path);
    }

    public function testGenerate()
    {
        $actual = $this->router->generate('', []);
        $this->assertFalse($actual);
    }

    public function testError()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $globals = [
            'argv' => [
                'php',
                'get'
            ],
            'argc' => 2
        ];
        $this->router->match($globals, []);
    }
}
