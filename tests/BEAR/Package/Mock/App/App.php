<?php

namespace BEAR\Package\Mock\App;

use BEAR\Sunday\Extension\Application\AppInterface;

final class App implements AppInterface
{
    public $name = __NAMESPACE__;
    public $path = __DIR__;
}
