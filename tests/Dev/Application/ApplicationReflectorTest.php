<?php
namespace BEAR\Package\Dev\Application;

use Aura\Di\Exception;
use Demo\Helloworld\Module\AppModule;
use Demo\Sandbox\Resource\Page\Hello\World;
use Demo\Sandbox\Resource\Page\Index;
use Ray\Di\Injector;

class ApplicationReflectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApplicationReflector
     */
    private $appReflector;

    public static function setUpBeforeClass()
    {
        @mkdir(__DIR__ . '/NotWritable');
        chmod(__DIR__ . '/NotWritable', 0555);
    }

    public static function tearDownAfterClass()
    {
    }

    protected function setUp()
    {
        static $app;

        if (! $app) {
            $app = Injector::create([new AppModule])->getInstance('BEAR\Sunday\Extension\Application\AppInterface');
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
    public function testGetResourcesClassName(array $resources)
    {
        $this->assertSame('Demo\Helloworld\Resource\Page\Hello', $resources['page://self/hello']['class']);
    }

    /**
     * @depends resources
     *
     * @covers BEAR\Package\Dev\Application\ApplicationReflector::getResources
     */
    public function testGetResourcesOptions(array $resources)
    {
        $expected = [
            'allow' => [
                0 => 'get',
            ],
            'param-get' => '(name)',
        ];
        $this->assertSame($expected, $resources['page://self/hello']['options']);
    }

    /**
     * @depends resources
     *
     * @covers BEAR\Package\Dev\Application\ApplicationReflector::getResources
     */
    public function testGetResourcesLinks(array $resources)
    {
        $expected = [];
        $this->assertSame($expected, $resources['page://self/hello']['links']);
    }

    /**
     * @covers BEAR\Package\Dev\Application\ApplicationReflector::newResource
     */
    public function testNewResource()
    {
        $uri = "page://self//one/two/three/resource";
        list(, $filePath) = $this->appReflector->newResource($uri);
        $contents = file_get_contents($filePath);
        $this->assertContains('class Resource', $contents);
        unlink($filePath);

        return [$contents, $filePath];
    }

    public function testNewResourceFileTopLevel()
    {
        $uri = "page://self/hello";
        list($filePath,) = $this->appReflector->getNewResource($uri);
        $this->assertContains('src/Resource/Page/Hello.php', $filePath);
    }

    /**
     * @depends testNewResource
     */
    public function testNewResourceFilePath(array $newResource)
    {
        $filePath = $newResource[1];
        $this->assertContains('src/Resource/Page/One/Two/Three/Resource.php', $filePath);
    }

    /**
     * @depends testNewResource
     */
    public function testNewResourceNameSpace(array $newResource)
    {
        $contents = $newResource[0];
        $this->assertContains('namespace Demo\Helloworld\Resource\Page\One\Two\Three;', $contents);
    }

    /**
     * @depends testNewResource
     */
    public function testNewResourceNameClass(array $newResource)
    {
        $contents = $newResource[0];
        $this->assertContains('class Resource extends ResourceObject', $contents);
    }

    public function testNewResourceTwice()
    {
        try {
            $uri = "page://self/new_for_test";
            list(,$filePath) = $this->appReflector->newResource($uri);
            $this->appReflector->newResource($uri);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\BEAR\Package\Dev\Application\Exception\FileAlreadyExists', $e);
        }
        if (isset($filePath) && file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * @expectedException \BEAR\Package\Dev\Application\Exception\InvalidUri
     */
    public function testGetNewResourceInvalidUri()
    {
        $uri = "invalid_uri///";
        $this->appReflector->getNewResource($uri);
    }

    /**
     * @expectedException \BEAR\Package\Dev\Application\Exception\NotWritable
     */
    public function testFilePutContents()
    {
        $Path = __DIR__ . '/NotWritable/new_dir/Resource.php';
        $this->appReflector->filePutContents($Path, '');
    }

    public function testGetResourceOptions()
    {
        $ro = new Index($_ENV['PACKAGE_DIR']);
        $options = $this->appReflector->getResourceOptions($ro);
        $this->assertSame(['allow', 'params'], array_keys($options));

        return $options;
    }

    public function testGetResourceOptionsDefaultValue()
    {
        $ro = new World;
        $options = $this->appReflector->getResourceOptions($ro);
        $expected = array (
            'allow' =>
            array (
                0 => 'get',
            ),
            'params' =>
            array (
                'param-get' => '(name)',
            ),
        );
        $this->assertSame($expected, $options);

        return $options;
    }

    public function testCompileAllResources()
    {
        $num = $this->appReflector->compileAllResources();
        $this->assertInternalType('integer', $num);
    }
}
