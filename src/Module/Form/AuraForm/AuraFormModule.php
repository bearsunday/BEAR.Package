<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Form\AuraForm;

use Ray\Di\AbstractModule;

class AuraFormModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind('Aura\Input\Form')->toProvider(__NAMESPACE__ . '\AuraFormProvider');
    }
}
