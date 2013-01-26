<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use BEAR\Sunday\Extension\ResourceView\TemplateEngineRendererInterface;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use BEAR\Resource\AbstractObject;
use Ray\Aop\Weave;
use ReflectionClass;
use Ray\Di\Di\Inject;

/**
 * Request renderer
 *
 * @package    BEAR.Sunday
 * @subpackage View
 */
class TemplateEngineRenderer implements TemplateEngineRendererInterface
{
    /**
     * Template engine adapter
     *
     * @var TemplateEngineAdapterInterface
     */
    private $templateEngineAdapter;

    /**
     * ViewRenderer Setter
     *
     * @param TemplateEngineAdapterInterface $templateEngineAdapter
     *
     * @Inject
     * @SuppressWarnings("long")
     */
    public function __construct(TemplateEngineAdapterInterface $templateEngineAdapter)
    {
        $this->templateEngineAdapter = $templateEngineAdapter;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.RenderInterface::render()
     * @SuppressWarnings("long")
     */
    public function render(AbstractObject $resourceObject)
    {
        if (is_scalar($resourceObject->body)) {
            $resourceObject->view = $resourceObject->body;
            return (string)$resourceObject->body;
        }

        $class = ($resourceObject instanceof Weave) ? get_class($resourceObject->___getObject()) : get_class($resourceObject);
        $file = (new ReflectionClass($class))->getFileName();

        // assign 'resource'
        $this->templateEngineAdapter->assign('resource', $resourceObject);

        // assign all
        if (is_array($resourceObject->body) || $resourceObject->body instanceof \Traversable) {
            $this->templateEngineAdapter->assignAll((array)$resourceObject->body);
        }
        $templateFileWithoutExtension = substr($file, 0, -3);
        $resourceObject->view = $this->templateEngineAdapter->fetch($templateFileWithoutExtension);

        return $resourceObject->view;
    }
}
