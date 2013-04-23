<?php

use BEAR\Resource\Request;
use BEAR\Resource\Invoker;
use BEAR\Resource\Linker;
use BEAR\Resource\NamedParams;
use BEAR\Resource\SignalParam;
use BEAR\Resource\Param;
use BEAR\Resource\Logger;
use Ray\Di\Config;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * return Request object
 */

$invoker = new Invoker(
    new Linker(new AnnotationReader),
    new NamedParams(
        new SignalParam(
            new Manager(new HandlerFactory, new ResultFactory, new ResultCollection),
            new Param
        )
    ),
    new Logger
);
return new Request(
    $invoker,
    new Manager(new HandlerFactory, new ResultFactory, new ResultCollection)
);
