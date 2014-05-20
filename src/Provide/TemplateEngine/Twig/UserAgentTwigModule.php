<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Twig;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;
use Aura\Web\Request\Client;

class UserAgentTwigModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->bind('BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface')
            ->to(__NAMESPACE__ . '\UserAgentTwigAdapter')
            ->in(Scope::SINGLETON);
        $this
            ->bind('Twig_Environment')
            ->toProvider(__NAMESPACE__ . '\TwigProvider')
            ->in(Scope::SINGLETON);
        $this
            ->bind('Aura\Web\Request\Client')
            ->toProvider('BEAR\Package\Provide\TemplateEngine\UserAgent\ClientProvider')
            ->in(Scope::SINGLETON);

    }
}
