<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\AuraView;

use BEAR\Package\Provide\TemplateEngine\AdapterTrait;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use Aura\View\AbstractTemplate;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class AuraViewAdapter implements TemplateEngineAdapterInterface
{
    use AdapterTrait;

    /**
     * File extension
     *
     * @var string
     */
    const EXT = 'tpl.php';

    /**
     * Template
     *
     * @var Template
     */
    private $view;

    /**
     * @var array
     */
    private $values;

    /**
     * @param AbstractTemplate $template
     *
     * @Inject
     */
    public function __construct(AbstractTemplate $template)
    {
        $this->view = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function assign($tplVar, $value)
    {
        $this->view->setData([
            [$tplVar => $value]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function assignAll(array $values)
    {
        $this->view->setData($values);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($tplWithoutExtension)
    {
        $templateFile = $tplWithoutExtension . self::EXT;
        $this->fileExists($templateFile);
        $rendered = $this->view->fetch($templateFile);

        return $rendered;
    }
}
