<?php

namespace Ray\Di\Compiler;

$instance = new \FakeVendor\HelloWorld\Resource\App\Emb_IggGcyM();
$instance->bindings = array('onGet' => array($singleton('BEAR\\Resource\\EmbedInterceptor-')));
$instance->setRenderer($prototype('BEAR\\Resource\\RenderInterface-'));
$is_singleton = false;
return $instance;
