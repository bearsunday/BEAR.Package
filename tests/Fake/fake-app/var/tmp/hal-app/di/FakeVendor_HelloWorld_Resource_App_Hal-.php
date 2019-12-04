<?php

namespace Ray\Di\Compiler;

$instance = new \FakeVendor\HelloWorld\Resource\App\Hal();
$instance->setRenderer($prototype('BEAR\\Resource\\RenderInterface-'));
$is_singleton = false;
return $instance;
