<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Form\AuraForm;

use Aura\Input\Form;
use Aura\Input\Builder;
use Aura\Input\Filter;
use Ray\Di\ProviderInterface;

class AuraFormProvider implements ProviderInterface
{
    /**
     * Return instance
     *
     * @return Form
     */
    public function get()
    {
        return new Form(new Builder, new Filter);
    }
}
