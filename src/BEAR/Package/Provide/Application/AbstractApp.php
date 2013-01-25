<?php
/**
 * Sandbox
 *
 * @package Sandbox
 */
namespace BEAR\Package\Provide\Application;

use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface;
use BEAR\Sunday\Extension\WebResponse\ResponseInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Package\Debug\ExceptionHandle\ExceptionHandlerInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\SignalHandler\Provides;
use Guzzle\Cache\CacheAdapterInterface;
use Ray\Di\Di\Inject;
use Ray\Di\InjectorInterface;
use Ray\Di\Di\Named;

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
abstract class AbstractApp implements AppInterface
{
    /**
     * Dependency injector
     *
     * @var \Ray\Di\InjectorInterface
     */
    public $injector;

    /**
     * Resource client
     *
     * @var \BEAR\Resource\ResourceInterface
     */
    public $resource;

    /**
     * Response
     *
     * @var BEAR\Sunday\Extension\WebResponse\ResponseInterface
     */
    public $response;

    /**
     * Exception handler
     *
     * @var \BEAR\Package\ExceptionHandle\ExceptionHandlerInterface
     */
    public $exceptionHandler;

    /**
     * Router
     *
     * @var \BEAR\Sunday\Extension\Router\RouterInterface
     */
    public $router;

    /**
     * Resource logger
     *
     * @var \BEAR\Resource\LoggerInterface
     */
    public $logger;

    /**
     * Response page object
     *
     * @var \BEAR\Resource\Object
     */
    public $page;

    /**
     * Constructor
     *
     * @param \Ray\Di\InjectorInterface                               $injector         Dependency Injector
     * @param \BEAR\Resource\ResourceInterface                        $resource         Resource client
     * @param \BEAR\Package\ExceptionHandle\ExceptionHandlerInterface $exceptionHandler Exception handler
     * @param \BEAR\Sunday\Application\Logger                         $logger           Application logger
     * @param BEAR\Sunday\Extension\WebResponse\ResponseInterface                      $response         Web / Console response
     * @param \BEAR\Sunday\Extension\Router\RouterInterface                        $router           Resource cache adapter
     *
     * @Inject
     */
    public function __construct(
        InjectorInterface $injector,
        ResourceInterface $resource,
        ExceptionHandlerInterface $exceptionHandler,
        ApplicationLoggerInterface $logger,
        ResponseInterface $response,
        RouterInterface $router
    ) {
        $this->injector = $injector;
        $this->resource = $resource;
        $this->response = $response;
        $this->exceptionHandler = $exceptionHandler;
        $this->logger = $logger;
        $this->router = $router;
        $resource->attachParamProvider('Provides', new Provides);
    }
}
