<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Twig;

use BEAR\Sunday\Inject\LibDirInject;
use BEAR\Sunday\Inject\TmpDirInject;
use Ray\Di\ProviderInterface as Provide;
use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * Twig
 *
 * @see http://twig.sensiolabs.org/
 */
class TwigProvider implements Provide
{
    use TmpDirInject;
    use LibDirInject;

    /**
     * Return instance
     *
     * @return \Twig_Environment
     */
    public function get()
    {
        $loader = new Twig_Loader_Filesystem(array('/', $this->libDir . '/twig/template'));
        $twig = new Twig_Environment($loader, array(
            'cache' => $this->tmpDir . '/twig/cache',
        ));
        return $twig;
    }
}
