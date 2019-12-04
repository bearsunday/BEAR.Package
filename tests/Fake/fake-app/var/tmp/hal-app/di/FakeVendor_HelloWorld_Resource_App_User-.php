<?php

namespace Ray\Di\Compiler;

$instance = new \FakeVendor\HelloWorld\Resource\App\User();
$instance->setRenderer($prototype('BEAR\\Resource\\RenderInterface-'));
$is_singleton = false;
return $instance;
