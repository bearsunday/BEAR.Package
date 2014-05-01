<?php

namespace BEAR\Package\Provide\ResourceView;

use BEAR\Package\Mock\ResourceObject\MockResource;
use BEAR\Package\Provide\TemplateEngine\Smarty\SmartyAdapter;
use BEAR\Resource\ResourceObject;
use Smarty;

class TemplateEngineRendererTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var TemplateEngineRenderer
     */
    private $templateEngineRenderer;

    /**
     * @var ResourceObject
     */
    private $resource;

    protected function setUp()
    {
        $smarty = new Smarty;
        $smarty->setCompileDir($_ENV['TMP_DIR']);
        $this->templateEngineRenderer = new TemplateEngineRenderer(new SmartyAdapter($smarty));
        $this->resource = new MockResource;
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Provide\ResourceView\TemplateEngineRenderer', $this->templateEngineRenderer);
    }

    public function testRender()
    {
        $this->templateEngineRenderer->render($this->resource);
        $this->assertContains('greeting is [hello].', $this->resource->view);
    }

    public function testRenderBodyScalar()
    {
        $this->resource->body = 'hello BEAR.Sunday';
        $this->templateEngineRenderer->render($this->resource);
        $this->assertContains('hello BEAR.Sunday', $this->resource->view);
    }
}
