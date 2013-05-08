<?php

namespace BEAR\Sunday\Tests;


/**
 * Test class for Annotation.
 */
class CliTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $systemRoot;

    protected function setUp()
    {
        parent::setUp();
        $this->systemRoot = dirname(__DIR__);
    }

    public function test_devWebPhp()
    {
        $cli = 'php ' . $this->systemRoot . '/public/web.php get /index';
        exec($cli, $return);
        $this->assertContains('<!DOCTYPE html>', $return);
    }

    public function test_devApiPhp()
    {
        $cli = 'php ' . $this->systemRoot . '/public/api.php get page://self/index';
        exec($cli, $return);
        $html = implode('', $return);
        $pos = strpos($html, 'Hello BEAR.Sunday');
        $this->assertTrue(is_int($pos), implode($return, "\n"), $html);
    }

    public function test_devApiPhpRep()
    {
        $cli = 'php ' . $this->systemRoot . '/public/api.php get page://self/index view';
        exec($cli, $return);
        $html = implode('', $return);
        $pos = strpos($html, 'Hello BEAR.Sunday');
        $this->assertTrue(is_int($pos));
    }

    public function test_devApiPhpValue()
    {
        $cli = 'php ' . $this->systemRoot . '/public/api.php get page://self/index value';
        exec($cli, $return);
        $html = implode('', $return);
        $pos = strpos($html, 'Hello BEAR.Sunday');
        $this->assertTrue(is_int($pos));
    }

    public function test_devApiPhpRequest()
    {
        $cli = 'php ' . $this->systemRoot . '/public/api.php get page://self/index request';
        exec($cli, $return);
        $html = implode('', $return);
        $pos = strpos($html, 'Hello BEAR.Sunday');
        $this->assertTrue(is_int($pos));
    }

    public function test_devOptions()
    {
        $cli = 'php ' . $this->systemRoot . '/public/api.php options page://self/blog/posts';
        exec($cli, $return);
        $html = implode('', $return);
        $pos = strpos($html, '["get","delete"]');
        $this->assertTrue(is_int($pos));
    }
}
