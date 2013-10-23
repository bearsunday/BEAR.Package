<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Smarty;

// @codingStandardsIgnoreFile
use BEAR\Sunday\Inject\AppDirInject;
use BEAR\Sunday\Inject\VendorDirInject;
use Ray\Di\ProviderInterface as Provide;
use Smarty;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Smarty3
 *
 * @see http://www.smarty.net/docs/
 */
class SmartyProvider implements Provide
{
    use TmpDirInject;
    use VendorDirInject;

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
        $appPlugin = $this->vendorDir . '/smarty/plugin/';
        $frameworkPlugin = __DIR__ . '/plugin';
        $smarty
            ->setCompileDir($this->tmpDir . '/smarty/template_c')
            ->setCacheDir($this->tmpDir . '/smarty/cache')
            ->setTemplateDir($this->vendorDir . '/smarty/template')
            ->setPluginsDir(array_merge($smarty->getPluginsDir(), [$appPlugin, $frameworkPlugin]) );
        $smarty->force_compile = false;
        $smarty->compile_check = false;

        return $smarty;
    }
}
