<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\PHPTAL;

use BEAR\Package\Provide\TemplateEngine\AdapterTrait;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

/**
 * PHPTAL adapter
 *
 * @package    BEAR.Package
 * @subpackage Module
 */
class PHPTALAdapter implements TemplateEngineAdapterInterface
{
    use AdapterTrait;

    /**
     * File extension
     *
     * @var string
     */
    const EXT = 'xhtml';

    /**
     * PHPTAL
     *
     * @var \PHPTAL
     */
    private $phptal;

    /**
     * @var array
     */
    private $values;

    /**
     * Constructor
     *
     * @param \PHPTAL $phptal
     *
     * @Inject
     */
    public function __construct(\PHPTAL $phptal)
    {
        $this->phptal = $phptal;
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
        $this->phptal->setTemplate($this->template);
        foreach($this->values as $k=>$v) {
            $this->phptal->set($k, $v);
        }
        $rendered = $this->phptal->execute();
        return $rendered;
    }
}
