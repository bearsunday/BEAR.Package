<?php
namespace BEAR\Package\tests\Dev;

use BEAR\Package\Dev\Application\ApplicationReflector;

class ApplicationReflectorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ApplicationReflector
     */
    private $appReflector;

    protected function setUp()
    {
        static $app;
        parent::setUp();
        if (! $app) {
            $app = require dirname(dirname(__DIR__)) . '/apps/Skeleton/scripts/instance.php';
        }
        $this->appReflector = new ApplicationReflector($app);

    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Dev\Application\ApplicationReflector', $this->appReflector);
    }

    /**
     * @test
     * @return array
     */
    public function resources()
    {
        $resources = $this->appReflector->getResources();
        $this->assertInternalType('array', $resources);

        return $resources;
    }

    /**
     * @depends resources
     *
     * @covers BEAR\Package\Dev\Application\ApplicationReflector::getResources
     */
    public function testGetResources(array $resources)
    {
        $this->assertSame(1, count($resources));
    }

    /**
     * @depends resources
     *
     * @covers BEAR\Package\Dev\Application\ApplicationReflector::getResources
     */
    public function testGetResourcesClassName(array $resources)
    {
        $this->assertSame('Skeleton\Resource\Page\Index', $resources['page://self/index']['class']);
    }

    /**
     * @depends resources
     *
     * @covers BEAR\Package\Dev\Application\ApplicationReflector::getResources
     */
    public function testGetResourcesOptions(array $resources)
    {
        $expected = [
            'allow' =>
            [
                0 => 'get',
            ],
            'param-get' => '(name)',
        ];
        $this->assertSame($expected, $resources['page://self/index']['options']);
    }

    /**
     * @depends resources
     *
     * @covers BEAR\Package\Dev\Application\ApplicationReflector::getResources
     */
    public function testGetResourcesLinks(array $resources)
    {
        $expected = [];
        $this->assertSame($expected, $resources['page://self/index']['links']);
    }
}
