<?php

namespace Ray\Di\Compiler;

$instance = new \FakeVendor\HelloWorld\Resource\App\Scalar();
$instance->setRenderer($singleton('BEAR\\Resource\\RenderInterface-'));
$is_singleton = false;
return $instance;
