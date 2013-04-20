<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Smarty;

use BEAR\Package\Provide\TemplateEngine\AdapterTrait;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use Smarty;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

/**
 * Smarty adapter
 *
 * @package    BEAR.Package
 * @subpackage Module
 */
class SmartyAdapter implements TemplateEngineAdapterInterface
{
    use AdapterTrait;

    /**
     * File extension
     *
     * @var string
     */
    const EXT = 'tpl';

    /**
     * smarty
     *
     * @var Smarty
     */
    private $smarty;

    /**
     * Constructor
     *
     * Smarty $smarty
     *
     * @Inject
     */
    public function __construct(Smarty $smarty)
    {
        $this->smarty = $smarty;

        $this->smarty->force_compile = false;
        $this->smarty->compile_check = false;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Sunday\Resource\View.TemplateEngineAdapter::assign()
     */
    public function assign($tplVar, $value)
    {
        $this->smarty->assign($tplVar, $value);
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Sunday\Resource\View.TemplateEngineAdapter::assignAll()
     */
    public function assignAll(array $values)
    {
        $this->smarty->assign($values);
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Sunday\View.Render::fetch()
     */
    public function fetch($tplWithoutExtension)
    {
        $this->template = $tplWithoutExtension . self::EXT;
        $this->fileExists($this->template);

        return $this->smarty->fetch($this->template);
    }
}
