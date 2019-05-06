<?php

namespace Ray\Di\Compiler;

$instance = new \FakeVendor_HelloWorld_Resource_Page_Emb_IggGcyM();
$instance->bindings = array('onGet' => array($singleton('BEAR\\Resource\\EmbedInterceptor-')));
$instance->setRenderer($singleton('BEAR\\Resource\\RenderInterface-'));
$is_singleton = false;
return $instance;
