<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Twig\Extension;

use Aura\Html\HelperLocatorFactory;

class AuraForm_Twig_Extension extends \Twig_Extension
{
    private static $helper;

    public function getName()
    {
        return 'AuraForm';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('form', [$this, 'createForm']),
        ];
    }

    public function createForm($hint)
    {
        if (! static::$helper) {
            $factory = new HelperLocatorFactory();
            static::$helper = $factory->newInstance();
        }

        $formElementHtml = static::$helper->input($hint);
        return $formElementHtml;
    }
}
