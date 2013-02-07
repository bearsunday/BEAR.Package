<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\WebResponse;

use BEAR\Sunday\Extension\ConsoleOutput\ConsoleOutputInterface;
use BEAR\Sunday\Extension\WebResponse\ResponseInterface;
use BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface as AppLogger;
use BEAR\Sunday\Exception\InvalidResourceType;
use BEAR\Sunday\Inject\LogInject;
use BEAR\Package\Provide\ConsoleOutput\ConsoleOutput;
use BEAR\Resource\Logger;
use BEAR\Resource\ObjectInterface as ResourceObject;

use BEAR\Resource\AbstractObject as Page;
use Symfony\Component\HttpFoundation\Response;
use Ray\Aop\Weave;
use Ray\Di\Di\Inject;
use Exception;

/**
 * Output with using Symfony HttpFoundation
 *
 * @package    BEAR.Sunday
 * @subpackage Web
 */
final class HttpFoundation implements ResponseInterface
{
    use LogInject;

    /**
     * Exception
     *
     * @var Exception
     */
    private $e;

    /**
     * Resource object
     *
     * @var \BEAR\Resource\AbstractObject
     */
    private $resource;

    /**
     * Response resource object
     *
     * @var Response
     */
    private $response;

    /**
     * @var int
     */
    private $code;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $view;

    /**
     * @var ConsoleOutputInterface
     */
    private $consoleOutput;

    /**
     * @var AppLogger
     */
    private $appLogger;

    /**
     * Set application logger
     *
     * @param AppLogger $appLogger
     *
     * @Inject
     */
    public function setAppLogger(AppLogger $appLogger)
    {
        $this->appLogger = $appLogger;
    }

    /**
     * Constructor
     *
     * @param ConsoleOutputInterface $consoleOutput
     *
     * @Inject
     */
    public function __construct(ConsoleOutputInterface $consoleOutput)
    {
        $this->consoleOutput = $consoleOutput;
    }

    /**
     * Set Resource
     *
     * @param mixed $resource BEAR\Resource\Object | Ray\Aop\Weaver $resource
     *
     * @throws InvalidResourceType
     * @return self
     */
    public function setResource($resource)
    {
        if ($resource instanceof Weave) {
            $resource = $resource->___getObject();
        }
        if ($resource instanceof ResourceObject === false && $resource instanceof Weave === false) {
            $type = (is_object($resource)) ? get_class($resource) : gettype($resource);
            throw new InvalidResourceType($type);
        }
        $this->resource = $resource;

        return $this;
    }

    /**
     * Set Exception
     *
     * @param \Exception $e
     * @param int        $exceptionId
     *
     * @return self
     */
    public function setException(Exception $e, $exceptionId)
    {
        $this->e = $e;
        $this->code = $e->getCode();
        $this->headers = [];
        $this->body = $exceptionId;

        return $this;
    }

    /**
     * Render
     *
     * @param Callable $renderer
     *
     * @return self
     */
    public function render(Callable $renderer = null)
    {
        if (is_callable($renderer)) {
            $this->view = $renderer($this->body);
        } else {
            $this->view = (string)$this->resource;
        }

        return $this;
    }

    /**
     * Make response object with RFC 2616 compliant HTTP header
     *
     * @return self
     * @deprecated
     */
    public function prepare()
    {
        trigger_error('unnecessary science 0.6.0', E_USER_DEPRECATED);
        return $this;
    }

    /**
     * Transfer representational state to http client (or console output)
     *
     * @return ResponseInterface
     */
    public function send()
    {
        $this->response = new Response($this->view, $this->resource->code, (array)$this->resource->headers);
        // compliant with RFC 2616.
        $this->response;

        if (PHP_SAPI === 'cli') {
            if ($this->resource instanceof Page) {
                $this->resource->headers = $this->response->headers->all();
            }
            $statusText = Response::$statusTexts[$this->resource->code];
            $this->consoleOutput->send($this->resource, $statusText, ConsoleOutput::MODE_REQUEST);
        } else {
            $this->response->send();
        }

        return $this;
    }
}
