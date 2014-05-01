<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Form\AuraForm;

use BEAR\Sunday\Inject\NamedArgsInject;
use Ray\Aop\MethodInvocation;
use Aura\Input\Form;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

trait AuraFormTrait
{
    use NamedArgsInject;

    /**
     * @param Form $form
     *
     * @Inject
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
        $this->filter = $this->form->getFilter();
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        list($args, $hasSubmit) = $this->getSubmit();
        $page = $invocation->getThis();

        $this->setForm($this->filter);
        $this->form->fill($args);
        if ($this->form->filter()) {
            // action
            return $invocation->proceed();
        }

        // set error message
        foreach ($this->form->getIterator() as $name => $value) {
            $page[$name] = $this->form->get($name);
            $errors = $this->form->getMessages($name);
            $error = ($hasSubmit && $errors)  ? $this->getErrorMessage($this->form->getMessages($name)) : '';
            $page->body['form'][$name]['error'] = $error;
        }

        return $page->onGet();
    }

    /**
     * @return array [$submit, $hasSubmit]
     */
    private function getSubmit()
    {
        if (isset($_POST['submit'])) {
            return [$_POST, true];
        } elseif (isset($_GET['submit'])) {
            return [$_GET, true];
        }
        return [[], false];

    }

    /**
     * @param array $errorMessages
     *
     * @return string
     */
    protected function getErrorMessage(array $errorMessages)
    {
        return implode(',', $errorMessages);
    }
}
