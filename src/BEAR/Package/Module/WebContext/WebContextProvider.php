<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\WebContext;

use Aura\Web\Context;
use Ray\Di\ProviderInterface;

/**
 * WebContext (Aura.Web)
 *
 * @see https://github.com/auraphp/Aura.Web.git
 */
class WebContextProvider implements ProviderInterface
{
    /**
     * Return instance
     *
     * @return Context
     */
    public function get()
    {
        return new Context($GLOBALS);
    }
}
