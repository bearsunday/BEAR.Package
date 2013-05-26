<?php

namespace Sandbox\Form;

use Aura\Input\Form as AuraForm;

class ContactForm extends AuraForm
{
    public function init()
    {
        $this->setField('name')->setAttribs(
            [
                'id' => 'name',
                'size' => 20,
                'maxlength' => 20,
            ]
        );
        $this->setField('email')->setAttribs(
            [
                'size' => 20,
                'maxlength' => 20,
            ]
        );
        $this->setField('url')->setAttribs(
            [
                'size' => 20,
                'maxlength' => 20,
            ]
        );
        $this->setField('message', 'textarea')->setAttribs(
            [
                'cols' => 40,
                'rows' => 5,
            ]
        );
        $this->setField('submit', 'submit')->setAttribs(['value' => 'send']);

        $filter = $this->getFilter();

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
                if (strlen($value) > 7) {
                    return true;
                }

                return false;
            }
        );
    }
}
