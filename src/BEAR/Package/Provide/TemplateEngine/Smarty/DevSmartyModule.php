<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Smarty;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;
use Smarty;
use Ray\Di\Di\Inject;

/**
 * Smarty module
 */
class DevSmartyModule
{
    private $smarty;

    /**
     * @param Smarty $smarty
     *
     * @Inject
     */
    public function setSmarty(Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * Configure smarty for development
     */
    protected function modify()
    {
        $this->smarty->force_compile = true;
        $this->smarty->compile_check = true;
    }
}
