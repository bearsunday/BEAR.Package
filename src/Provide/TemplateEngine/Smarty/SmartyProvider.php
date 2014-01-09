<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Smarty;

// @codingStandardsIgnoreFile
use BEAR\Sunday\Inject\TmpDirInject;
use BEAR\Sunday\Inject\LibDirInject;
use Ray\Di\ProviderInterface as Provide;
use Smarty;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Smarty3 instance provider
 *
 * @see http://www.smarty.net/docs/
 */
class SmartyProvider implements Provide
{
    use TmpDirInject;
    use LibDirInject;

    /**
     * Return instance
     *
     * @return Smarty
     */
    public function get()
    {
        static $smarty;

        if ($smarty) {
            return $smarty;
        }

        $smarty = new Smarty;
        $appPlugin = $this->libDir . '/smarty/plugin/';
        $frameworkPlugin = __DIR__ . '/plugin';
        $smarty
            ->setCompileDir($this->tmpDir . '/smarty/template_c')
            ->setCacheDir($this->tmpDir . '/smarty/cache')
            ->setTemplateDir($this->libDir . '/smarty/template')
            ->setPluginsDir(array_merge($smarty->getPluginsDir(), [$appPlugin, $frameworkPlugin]) );
        $smarty->force_compile = false;
        $smarty->compile_check = false;

        return $smarty;
    }
}
