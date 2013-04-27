<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Package\Dev;

use BEAR\Package\Dev\Web\Web;

class DevTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Dev
     */
    private $dev;

    public function setUp()
    {
        $argv = [];
        $server = ['REQUEST_URI' => '/dev/'];
        $this->dev = new Dev($argv, $server, new Web, 'apache');
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Dev\Dev', $this->dev);
    }

    public function testAddApp()
    {
        $app = require $GLOBALS['_BEAR_PACKAGE_DIR'] . '/apps/Helloworld/scripts/instance.php';
        $dev = $this->dev->setApp($app);
        $this->assertInstanceOf('BEAR\Package\Dev\Dev', $dev);
    }

    public function testWebService()
    {
        $app = require $GLOBALS['_BEAR_PACKAGE_DIR'] . '/apps/Helloworld/scripts/instance.php';
        list($code, $html) = $this->dev->setReturnMode()->setApp($app)->webService('/dev/');
        $this->assertSame(200, $code);
        $this->assertContains('Helloworld Dev</title>', $html);
    }
}
