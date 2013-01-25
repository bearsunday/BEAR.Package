<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\ProvideExtra\TemplateEngine\Twig;

use BEAR\Sunday\Resource\View\TemplateEngineAdapter;
use Twig_Environment;
use BEAR\Sunday\Exception\TemplateNotFound;
use Twig;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

/**
 * Smarty adapter
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class TwigAdapter implements TemplateEngineAdapter
{
    /**
     * Twig
     *
     * @var Twig
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
     * Is production ?
     *
     * @param bool $isProd
     *
     * @Inject
     * @Named("is_prod")
     */
//    public function setIsProd($isProd)
//    {
//        $this->isProd = $isProd;
//    }

    /**
     * Constructor
     *
     * Smarty $smarty
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
