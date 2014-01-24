<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\AuraView;

use Aura\View\Template;
use Aura\View\EscaperFactory;
use Aura\View\TemplateFinder;
use Aura\View\HelperLocator;
use BEAR\Sunday\Inject\LibDirInject;
use BEAR\Sunday\Inject\TmpDirInject;
use Ray\Di\ProviderInterface as Provide;

/**
 * Aura.View
 *
 * @see https://github.com/auraphp/Aura.View
 */
class AuraViewProvider implements Provide
{
    /**
     * Return instance
     *
     * @return Template
     */
    public function get()
    {
        $template = new Template(
            new EscaperFactory,
            new TemplateFinder,
            new HelperLocator
        );

        return $template;
    }
}
