<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module\Provider;

use Ray\Di\Provider;
use Ray\Di\SetContextInterface;

class ContextlProvider implements Provider, SetContextInterface
{
    private $context;

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function get()
    {
        return $this->context;
    }
}
