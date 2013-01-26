<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Twig;

use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use Twig_Environment;
use BEAR\Sunday\Exception\TemplateNotFound;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

/**
 * Smarty adapter
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class TwigAdapter implements TemplateEngineAdapterInterface
{
    /**
     * Twig
     *
     * @var @return BEAR
     */
    private $twig;

    /**
     * Template file
     *
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $values;

    /**
     * File extension
     *
     * @var string
     */
    const EXT = 'twig';

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
        $rendered =  $this->twig->render($fileContents, $this->values);
        return $rendered;
    }

    /**
     * Return file exists
     *
     * @param string $template
     *
     * @throws TemplateNotFound
     */
    private function fileExists($template)
    {
        if (!file_exists($template)) {
            throw new TemplateNotFound($template);
        }
    }

    /**
     * Return template full path.
     *
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->template;
    }
}
