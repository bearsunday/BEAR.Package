<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Smarty;

use BEAR\Package\Provide\TemplateEngine\AdapterTrait;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use Smarty;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

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
     * Smarty $smarty
     *
     * @Inject
     */
    public function __construct(Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * {@inheritdoc}
     */
    public function assign($tplVar, $value)
    {
        $this->smarty->assign($tplVar, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function assignAll(array $values)
    {
        $this->smarty->assign($values);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($tplWithoutExtension)
    {
        $this->template = $tplWithoutExtension . self::EXT;
        $this->fileExists($this->template);

        return $this->smarty->fetch($this->template);
    }
}
