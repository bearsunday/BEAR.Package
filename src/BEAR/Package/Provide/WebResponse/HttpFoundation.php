<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\WebResponse;

use BEAR\Package\Provide\ConsoleOutput\ConsoleOutput;
use BEAR\Resource\ResourceObject as Page;
use BEAR\Resource\Logger;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\RenderInterface;
use BEAR\Sunday\Exception\InvalidResourceType;
use BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface as AppLogger;
use BEAR\Sunday\Extension\ConsoleOutput\ConsoleOutputInterface;
use BEAR\Sunday\Extension\WebResponse\ResponseInterface;
use BEAR\Sunday\Inject\LogInject;
use Symfony\Component\HttpFoundation\Response;
use Ray\Aop\Weave;
use Ray\Di\Di\Inject;

/**
 * Output with using Symfony HttpFoundation
 */
final class HttpFoundation implements ResponseInterface
{
    use LogInject;

    /**
     * Resource object
     *
     * @var \BEAR\Resource\ResourceObject
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
     * @var bool
     */
    private $isCli;

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
        $this->isCli = (PHP_SAPI === 'cli');
    }

    /**
     * @param $isCli
     *
     * @return self
     */
    public function setIsCli($isCli)
    {
        $this->isCli = $isCli;

        return $this;
    }

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
        if ($resource instanceof ResourceObject === false) {
            $type = (is_object($resource)) ? get_class($resource) : gettype($resource);
            throw new InvalidResourceType($type);
        }
        $this->resource = $resource;

        return $this;
    }

    /**
     * Render
     *
     * @param RenderInterface $renderer
     *
     * @return $this
     */
    public function render(RenderInterface $renderer = null)
    {
        $this->view = is_null($renderer) ?  (string)$this->resource : $renderer->render($this->resource);

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

        if (! $this->isCli) {
            $this->response->send();
            return $this;
        }

        if ($this->resource instanceof Page) {
            $this->resource->headers = $this->response->headers->all();
        }
        $statusText = Response::$statusTexts[$this->resource->code];
        $this->consoleOutput->send($this->resource, $statusText);

        return $this;
    }
}
