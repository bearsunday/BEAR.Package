<?php

namespace BEAR\Package\Provide\TemplateEngine;

use BEAR\Package\Provide\TemplateEngine\Twig\TwigAdapter;
use Twig_Environment;
use Twig_Loader_Filesystem;

class TwigAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwigAdapter
     */
    private $TwigAdapter;

    /**
     * @var string
     */
    private $tpl;

    protected function setUp()
    {
        $loader = new Twig_Loader_Filesystem(array('/', __DIR__));
        $twig = new Twig_Environment($loader);
        $this->TwigAdapter = new TwigAdapter($twig);
        $this->tpl = __DIR__ . '/test.';
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Package\Provide\TemplateEngine\Twig\TwigAdapter', $this->TwigAdapter);
    }

    public function testAssign()
    {
        $this->TwigAdapter->assign('greeting', 'adios');
        $result = $this->TwigAdapter->fetch($this->tpl);
        $this->assertSame('greeting is adios', $result);
    }

    public function testAssignAll()
    {
        $this->TwigAdapter->assignAll(['greeting' => 'adios']);
        $result = $this->TwigAdapter->fetch($this->tpl);
        $this->assertSame('greeting is adios', $result);
    }

    /**
     * @expectedException \BEAR\Package\Provide\TemplateEngine\Exception\TemplateNotFound
     */
    public function testTemplateNotExists()
    {
        $this->TwigAdapter->assignAll(['greeting' => 'adios']);
        $this->TwigAdapter->fetch('INVALID');
    }

    public function testGetTemplate()
    {
        $this->TwigAdapter->assignAll(['greeting' => 'adios']);
        $this->TwigAdapter->fetch($this->tpl);
        $templateFile = $this->TwigAdapter->getTemplateFile();
        $this->assertSame(__DIR__ . '/test.twig', $templateFile);
    }
}
