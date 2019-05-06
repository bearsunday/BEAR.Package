<?php

namespace Ray\Di\Compiler;

$instance = new \FakeVendor_HelloWorld_Resource_App_Post_dEEYNAA();
$instance->bindings = array('onPost' => array($singleton('BEAR\\Package\\Provide\\Representation\\CreatedResourceInterceptor-')));
$instance->setRenderer($singleton('BEAR\\Resource\\RenderInterface-'));
$instance->setResource($singleton('BEAR\\Resource\\ResourceInterface-'));
$is_singleton = false;
return $instance;
