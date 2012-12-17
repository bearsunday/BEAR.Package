<?php
/**
 * Sandbox
 *
 * @package Sandbox
 */
namespace Sandbox;

use BEAR\Sunday\Application\Context;
use Ray\Di\Injector;
use Ray\Di\Di\Inject;
use Ray\Di\InjectorInterface;
use Ray\Di\Di\Named;
use BEAR\Sunday\Web\ResponseInterface;
use BEAR\Sunday\Application\Logger as ApplicationLogger;
use BEAR\Sunday\Web\RouterInterface;
use BEAR\Sunday\Web\GlobalsInterface;
use BEAR\Package\Debug\ExceptionHandle\ExceptionHandlerInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\SignalHandler\Provides;
use Guzzle\Common\Cache\CacheAdapterInterface;

require_once dirname(dirname(__DIR__)) . '/vendor/smarty/smarty/distribution/libs/Smarty.class.php';
require_once dirname(dirname(__DIR__)) . '/vendor/twig/twig/lib/Twig/Autoloader.php';
\Twig_Autoloader::register();

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
     * @var \BEAR\Sunday\Web\ResponseInterface
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
     * @var \BEAR\Sunday\Web\RouterInterface
     */
    public $router;

    /**
     * Global
     *
     * @var \BEAR\Sunday\Web\GlobalsInterface
     */
    public $globals;

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
     * @param \BEAR\Sunday\Web\ResponseInterface                      $response         Web / Console response
     * @param \BEAR\Sunday\Web\RouterInterface                        $router           Resource cache adapter
     * @param \BEAR\Sunday\Web\GlobalsInterface                       $globals          GLOBALS value
     *
     * @Inject
     */
    public function __construct(
        InjectorInterface $injector,
        ResourceInterface $resource,
        ExceptionHandlerInterface $exceptionHandler,
        ApplicationLogger $logger,
        ResponseInterface $response,
        RouterInterface $router,
        GlobalsInterface $globals
    ) {
        $this->injector = $injector;
        $this->resource = $resource;
        $this->response = $response;
        $this->exceptionHandler = $exceptionHandler;
        $this->logger = $logger;
        $this->router = $router;
        $this->globals = $globals;
        $resource->attachParamProvider('Provides', new Provides);
    }
}
