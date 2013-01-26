<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Twig;

use BEAR\Sunday\Inject\TmpDirInject;
use BEAR\Sunday\Inject\AppDirInject;
use Ray\Di\ProviderInterface as Provide;
use Twig_Environment;
use Twig_Loader_String;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Twig
 *
 * @see http://www.smarty.net/docs/ja/
 */
class TwigProvider implements Provide
{
    use TmpDirInject;
    use AppDirInject;

    /**
     * Return instance
     *
     * @return \Twig_Environment
     */
    public function get()
    {
        $twig = new Twig_Environment(new Twig_Loader_String);
        return $twig;
    }
}
