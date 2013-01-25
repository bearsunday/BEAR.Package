<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine;

use BEAR\Sunday\Inject\TmpDirInject;
use BEAR\Sunday\Inject\AppDirInject;
use Ray\Di\ProviderInterface as Provide;
use Smarty;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

// @codingStandardsIgnoreFile

/**
 * Smarty3
 *
 * @see http://www.smarty.net/docs/ja/
 */
class SmartyProvider implements Provide
{
    use TmpDirInject;
    use AppDirInject;

    /**
     * Return instance
     *
     * @return Smarty
     */
    public function get()
    {
        $smarty = new Smarty;
        $appPlugin = $this->appDir . '/vendor/libs/smarty/plugin/';
        $frameworkPlugin = __DIR__ . '/plugin';
        $smarty
        ->setCompileDir($this->tmpDir . '/smarty/template_c')
        ->setCacheDir($this->tmpDir . '/smarty/cache')
        ->setTemplateDir($this->appDir . '/Resource/View')
        ->setPluginsDir([$appPlugin, $frameworkPlugin]);

        return $smarty;
    }
}
