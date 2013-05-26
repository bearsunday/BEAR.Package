<?php

namespace BEAR\Sunday\Tests;

use Sandbox\Form\ContactForm;
use Aura\Input\Builder;
use Aura\Input\Filter;

/**
 * Test class for Annotation.
 */
class AuraFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Sandbox\Form\ContactForm
     */
    private $form;

    protected function setUp()
    {
        $this->form = new ContactForm(new Builder, new Filter);
    }

    public function testNew()
    {
        $this->assertInstanceOf('Sandbox\Form\ContactForm', $this->form);
    }

    public function testValidate()
    {
        $data = [];
        $this->form->fill($data);
        $result = $this->form->filter();
        $this->assertFalse($result);

        return  $this->form;
    }

    /**
     * @depends testValidate
     */
    public function testValidateErrorMessage($form)
    {
        $errorName = $form->getMessages('name');
        $this->assertSame('Name must be alphabetic only.', $errorName[0]);
    }
}
