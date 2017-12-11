<?php
namespace MyVendor\MyProject;

use BEAR\Package\Bootstrap;
use BEAR\Sunday\Extension\Application\AbstractApp;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    /**
     * @dataProvider
     */
    public function contextsProvider()
    {
        return [
            ['prod-hal-api-app'],
        ];
    }

    /**
     * @dataProvider contextsProvider
     */
    public function testNewApp(string $contexts)
    {
        $app = (new Bootstrap())->getApp(__NAMESPACE__, $contexts);
        $this->assertInstanceOf(AbstractApp::class, $app);
    }
}
