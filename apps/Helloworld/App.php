<?php
/**
 * Helloworld
 *
 * @package Helloworld
 */
namespace Helloworld;

use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Di\Injector;
use Ray\Di\Di\Inject;
use Ray\Di\InjectorInterface;
use Ray\Di\Di\Named;
use BEAR\Sunday\Extension\WebResponse\ResponseInterface;
use BEAR\Sunday\Exception\ExceptionHandlerInterface;
use BEAR\Sunday\Application\Logger as ApplicationLogger;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\SignalHandler\Provides;
use Guzzle\Cache\CacheAdapterInterface;

/**
 * Application
 *
 * available run mode:
 *
 * 'Prod'
 * 'Api'
 * 'Dev'
 * 'Stab;
 * 'Test'
 *
 * @package Sandbox
 */
final class App implements Context
{
    /** application dir path @var string */
    const DIR = __DIR__;

    /**
     * Dependency injector
     *
     * @var Ray\Di\Injector
     */
    public $injector;

    /**
     * Resource client
     *
     * @var BEAR\Resource\Resource
     */
    public $resource;

    /**
     * Response
     *
     * @var ResponseInterface
     */
    public $response;

    /**
     * Cache
     *
     * @var unknown_type
     */
    private $cache;

    /**
     * Exception handler
     *
     * @var EexceptionHandle
     */
    public $exceptionHandler;

    /**
     * Resource logger
     *
     * @var BEAR\Resource\Logger
     */
    public $logger = [];

    /**
     * Constructor
     *
     * @param InjectorInterface         $injector         Dependency Injector
     * @param ResourceInterface         $resource         Resource client
     * @param ExceptionHandlerInterface $exceptionHandler Exception handler
     * @param ResponseInterface         $response         Web / Console response
     * @param CacheAdapterInterface     $cache            Resource cache adapter
     * @param ApplicationLogger         $logger           Application logger
     *
     * @Inject
     * @Named("cache=resource_cache")
     */
    public function __construct(
        InjectorInterface $injector,
        ResourceInterface $resource,
        ExceptionHandlerInterface $exceptionHandler,
        ResponseInterface $response,
        CacheAdapterInterface $cache = null,
        ApplicationLogger $logger = null
    ) {
        $this->injector = $injector;
        $this->resource = $resource;
        $this->response = $response;
        $this->exceptionHandler = $exceptionHandler;
        $this->cache = $cache;
        $this->logger = $logger;
        $resource->attachParamProvider('Provides', new Provides);
        if ($this->cache instanceof CacheAdapterInterface) {
            $resource->setCacheAdapter($this->cache);
        }
    }
}
