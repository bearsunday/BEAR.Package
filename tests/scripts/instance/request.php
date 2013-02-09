<?php

use BEAR\Resource\Request;
use BEAR\Resource\Invoker;
use BEAR\Resource\Linker;
use Ray\Di\Config;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use Doctrine\Common\Annotations\AnnotationReader as Reader;

/**
 * return Request object
 */
return new Request(
    new Invoker(new Config(new Annotation(new Definition, new Reader)), new Linker(new Reader),
    new Manager(new HandlerFactory, new ResultFactory, new ResultCollection))
);
