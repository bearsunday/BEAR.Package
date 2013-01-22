<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package    BEAR.Sunday
 * @subpackage Exception
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Debug\ExceptionHandle;

use BEAR\Resource\Exception\BadRequest;
use Ray\Di\InjectorInterface;
use Ray\Di\AbstractModule;
use BEAR\Resource\AbstractObject as ResourceObject;
use BEAR\Resource\Exception\ResourceNotFound;
use BEAR\Resource\Exception\MethodNotAllowed;
use BEAR\Resource\Exception\Parameter;
use BEAR\Resource\Exception\Scheme;
use BEAR\Resource\Exception\Uri;
use BEAR\Sunday\Resource\Page\Error;
use BEAR\Sunday\Web\ResponseInterface;
use BEAR\Sunday\Inject\LogDirInject;
use Exception;
use Ray\Di\Exception\Binding;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Exception handler for development
 *
 * @package    BEAR.Sunday
 * @subpackage Exception
 */
final class ExceptionHandler implements ExceptionHandlerInterface
{
    use LogDirInject;

    /**
     * Response
     *
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var Error
     */
    private $errorPage;

    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @var string
     */
    private $viewTemplate;

    /**
     * Error message
     *
     * @var array
     */
    private $message = [
        'ResourceNotFound' => 'The requested URI was not found on this service.',
        'BadRequest' => 'You sent a request that this service could not understand.',
        'Parameter' => 'You sent a request that query is not valid.',
        'Scheme' => 'You sent a request that scheme is not valid.',
        'MethodNotAllowed' => 'The requested method is not allowed for this URI.'
    ];

    /**
     * Set message
     *
     * @param array $message
     *
     * @Inject(optional = true);
     */
    public function setMessage(array $message)
    {
        $this->message = $message;
    }

    /**
     * Set Injector for logging
     *
     * @param \Ray\Di\InjectorInterface $injector
     *
     * @Inject(optional = true);
     */
    public function setModule(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }

    /**
     * Set response
     *
     * @param mixed             $exceptionTpl
     * @param ResponseInterface $response
     * @param ResourceObject    $errorPage
     *
     * @Inject
     * @Named("exceptionTpl=exceptionTpl,errorPage=errorPage")
     */
    public function __construct(
        $exceptionTpl = null,
        ResponseInterface $response,
        ResourceObject $errorPage = null
    ) {
        $this->viewTemplate = $exceptionTpl;
        $this->response = $response;
        $this->errorPage = $errorPage ? : new Error;
    }

    /**
     * (non-PHPdoc)
     *
     * @see BEAR\Package\Exception.ExceptionHandlerInterface::handle()
     */
    public function handle(Exception $e)
    {
        $page = $this->buildErrorPage($e, $this->errorPage);
        $id = $page->headers['X-EXCEPTION-ID'];
        $this->writeExceptionLog($e, $id);
        $this->response->setResource($page)->render()->prepare()->send();
        exit(1);
    }

    /**
     * Return error page
     *
     * @param                               $e
     * @param \BEAR\Resource\AbstractObject $response
     *
     * @return \BEAR\Resource\AbstractObject
     * @throws
     */
    private function buildErrorPage($e, ResourceObject $response)
    {
        $exceptionId = 'e' . $response->code . '-' . substr(md5((string)$e), 0, 5);
        try {
            throw $e;
        } catch (ResourceNotFound $e) {
            $response->code = 404;
            $response->view = $this->message['ResourceNotFound'];
            goto NOT_FOUND;
        } catch (Parameter $e) {
            $response->code = 400;
            $response->view = $this->message['Parameter'];
            goto BAD_REQUEST;
        } catch (Scheme $e) {
            $response->code = 400;
            $response->view = $this->message['Scheme'];
            goto BAD_REQUEST;
        } catch (MethodNotAllowed $e) {
            $response->code = 405;
            $response->view = $this->message['MethodNotAllowed'];
            goto METHOD_NOT_ALLOWED;
        } catch (Binding $e) {
            goto INVALID_BINDING;
        } catch (Uri $e) {
            $response->code = 400;
            goto INVALID_URI;
        } catch (BadRequest $e) {
            $response->code = 400;
            $response->view = $this->message['BadRequest'];
            goto METHOD_NOT_ALLOWED;
        } catch (Exception $e) {
            $response->view = "Internal error occurred ({$exceptionId})";
            goto SERVER_ERROR;
        }

        INVALID_BINDING:
        SERVER_ERROR:
        $response->code = 500;

        NOT_FOUND:
        BAD_REQUEST:
        METHOD_NOT_ALLOWED:
        INVALID_URI:


        if (PHP_SAPI === 'cli') {
        } else {
            $response->view = $this->getView($e);
        }
        $response->headers['X-EXCEPTION-CLASS'] = get_class($e);
        $response->headers['X-EXCEPTION-MESSAGE'] = str_replace(PHP_EOL, ' ', $e->getMessage());
        $response->headers['X-EXCEPTION-CODE-FILE-LINE'] = '(' . $e->getCode() . ') ' . $e->getFile(
        ) . ':' . $e->getLine();
        $previous = $e->getPrevious() ? (get_class($e->getPrevious()) . ': ' . str_replace(
            PHP_EOL,
            ' ',
            $e->getPrevious()->getMessage()
        )) : '-';
        $response->headers['X-EXCEPTION-PREVIOUS'] = $previous;
        $response->headers['X-EXCEPTION-ID'] = $exceptionId;
        $response->headers['X-EXCEPTION-ID-FILE'] = $this->getLogFilePath($exceptionId);


        return $response;
    }

    private function getView(\Exception $e)
    {
        // exception screen in develop
        if (isset($this->injector)) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $dependencyBindings = (string)$this->injector;
            /** @noinspection PhpUnusedLocalVariableInspection */
            $modules = $this->injector->getModule()->modules;
        } elseif (isset($e->module)) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $dependencyBindings = (string)$e->module;
            /** @noinspection PhpUnusedLocalVariableInspection */
            $modules = $e->module->modules;
        } else {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $dependencyBindings = 'n/a';
            /** @noinspection PhpUnusedLocalVariableInspection */
            $modules = 'n/a';
        }
        /** @noinspection PhpIncludeInspection */
        return include $this->viewTemplate;
    }

    /**
     * Write exception logs
     *
     * @param Exception $e
     * @param string    $exceptionId
     */
    public function writeExceptionLog(Exception $e, $exceptionId)
    {
        $data = (string)$e;
        $previousE = $e->getPrevious();
        if ($previousE) {
            $data .= PHP_EOL . PHP_EOL . '-- Previous Exception --' . PHP_EOL . PHP_EOL;
            $data .= $previousE->getTraceAsString();
        }
        $data .= PHP_EOL . PHP_EOL . '-- Bindings --' . PHP_EOL . (string)$this->injector;
        $file = $this->getLogFilePath($exceptionId);
        if (is_writable($this->logDir)) {
            file_put_contents($file, $data);
        } else {
            error_log("{$file} is not writable");
        }
    }

    /**
     * Return log file path
     *
     * @param $exceptionId
     *
     * @return string
     */
    private function getLogFilePath($exceptionId)
    {
        return "{$this->logDir}/{$exceptionId}.log";
    }
}
