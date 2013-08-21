<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use BEAR\Resource\AbstractObject;
use BEAR\Sunday\Extension\ResourceView\TemplateEngineRendererInterface;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use Ray\Aop\Weave;
use Ray\Aop\WeavedInterface;
use ReflectionClass;
use Ray\Di\Di\Inject;

/**
 * Request renderer
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
     * {@inheritdoc}
     * @SuppressWarnings("long")
     */
    public function render(AbstractObject $resourceObject)
    {
        if (is_scalar($resourceObject->body)) {
            $resourceObject->view = $resourceObject->body;
            return (string)$resourceObject->body;
        }
        if ($resourceObject instanceof WeavedInterface) {
            $file = (new ReflectionClass($resourceObject))->getParentClass()->getFileName();
        } else {
            $file = (new ReflectionClass($resourceObject))->getFileName();
        }

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
