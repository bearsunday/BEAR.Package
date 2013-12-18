<?php

namespace Demo\Sandbox\Interceptor\Form;

use BEAR\Sunday\Inject\NamedArgsInject;
use Ray\Aop\MethodInterceptor;
use Aura\Input\FilterInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use BEAR\Package\Module\Form\AuraForm\AuraFormTrait;

/**
 * Aura.Input form
 *
 * @see https://github.com/auraphp/Aura.Input
 */
class AuraContact implements MethodInterceptor
{
    use AuraFormTrait;

    /**
     * Set form
     *
     * @param FilterInterface $filter
     */
    private function setForm(FilterInterface &$filter)
    {
        $this->form
            ->setField('name')
            ->setAttribs(
                [
                    'id' => 'name',
                    'size' => 20,
                    'maxlength' => 20
                ]
            );
        $this->form
            ->setField('email')
            ->setAttribs(
                [
                    'size' => 20,
                    'maxlength' => 20,
                ]
            );
        $this->form
            ->setField('url')
            ->setAttribs(
                [
                    'size' => 20,
                    'maxlength' => 20,
                ]
            );
        $this->form
            ->setField('message', 'textarea')
            ->setAttribs(
                [
                    'cols' => 40,
                    'rows' => 5,
                ]
            );

        $filter->setRule(
            'name',
            'Name must be alphabetic only.',
            function ($value) {
                return ctype_alpha($value);
            }
        );

        $filter->setRule(
            'email',
            'Enter a valid email address',
            function ($value) {
                return filter_var($value, FILTER_VALIDATE_EMAIL);
            }
        );

        $filter->setRule(
            'url',
            'Enter a valid url',
            function ($value) {
                return filter_var($value, FILTER_VALIDATE_URL);
            }
        );

        $filter->setRule(
            'message',
            'Message should be more than 7 characters',
            function ($value) {
                return (strlen($value) > 7) ? true : false;
            }
        );

        $this->form
            ->setField('submit', 'submit')
            ->setAttribs(['value' => 'send']);
    }
}
