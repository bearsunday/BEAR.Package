<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Twig;

use BEAR\Package\Provide\TemplateEngine\AdapterTrait;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use Twig_Environment;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

/**
 * Smarty adapter
 *
 * @package    BEAR.Package
 * @subpackage Module
 */
class TwigAdapter implements TemplateEngineAdapterInterface
{
    use AdapterTrait;

    /**
     * File extension
     *
     * @var string
     */
    const EXT = 'twig';

    /**
     * Twig
     *
     * @var @return BEAR
     */
    private $twig;

    /**
     * @var array
     */
    private $values;

    /**
     * Constructor
     *
     * @param Twig_Environment $twig
     *
     * @Inject
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Sunday\Resource\View.TemplateEngineAdapter::assign()
     */
    public function assign($tplVar, $value)
    {
        $this->values[$tplVar] = $value;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Sunday\Resource\View.TemplateEngineAdapter::assignAll()
     */
    public function assignAll(array $values)
    {
        $this->values = $values;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Sunday\View.Render::fetch()
     */
    public function fetch($tplWithoutExtension)
    {
        $this->template = $tplWithoutExtension . self::EXT;
        $this->fileExists($this->template);
        $fileContents = file_get_contents($this->template);
        $rendered = $this->twig->render($fileContents, $this->values);

        return $rendered;
    }
}
