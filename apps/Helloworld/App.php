<?php
/**
 * Helloworld
 *
 * @package Skeleton
 */
namespace Helloworld;

use BEAR\Package\Provide\Application\AbstractApp;
use BEAR\Resource\ResourceInterface;
use Ray\Di\Di\Inject;

/**
 * Application
 *
 * @package Skeleton
 */
final class App extends AbstractApp
{
    /** application dir path @var string */
    const DIR = __DIR__;

    /**
     * @param \BEAR\Resource\ResourceInterface $resource
     *
     * @Inject
     */
    public function __construct(
        ResourceInterface $resource
    ) {
        $this->resource = $resource;
    }
}
