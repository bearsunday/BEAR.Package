<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Provide\Error\ErrorPage as CliErrorPage;
use BEAR\Resource\Code;
use BEAR\Sunday\Extension\Error\ErrorInterface;
use BEAR\Sunday\Extension\Router\RouterMatch as Request;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use BEAR\Sunday\Provide\Error\ErrorPage;

/**
 * vnd.error for BEAR.Package
 *
 * @see https://github.com/blongden/vnd.error
 */
class VndErrorHandler implements ErrorInterface
{
    /**
     * @var int
     */
    private $code;

    /**
     * @var array
     */
    private $body = ['message' => '', 'logref' => ''];

    /**
     * @var string
     */
    private $logDir;

    /**
     * @var ErrorPage
     */
    private $errorPage;

    /**
     * @var TransferInterface
     */
    private $responder;

    /**
     * @var string
     */
    private $lastErrorFile;

    /**
     * @var ExceptionAsString
     */
    private $exceptionString;

    public function __construct(AbstractAppMeta $appMeta, TransferInterface $responder)
    {
        $this->logDir = $appMeta->logDir;
        $this->lastErrorFile = $this->logDir . '/last_error.log';
        $this->responder = $responder;
        $this->exceptionString = new ExceptionAsString;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(\Exception $e, Request $request)
    {
        $this->errorPage = $this->getErrorPage($e, $this->lastErrorFile);

        $isCodeError = array_key_exists($e->getCode(), (new Code)->statusText);
        $code = $isCodeError ? $e->getCode() : Code::ERROR;
        $message = $code . ' ' . (new Code)->statusText[$code];
        // Client error
        if (400 <= $code && $code < 500) {
            $this->log($e, $request);
            $this->code = $code;
            $this->body = [
                'message' => $message
            ];

            return $this;
        }
        // Server error
        $logRef = $this->log($e, $request);
        $this->code = $code;
        $this->body = [
            'message' => $message,
            'logref' => $logRef
        ];

        return $this;
    }

    /**
     * @param \Exception $e
     * @param string     $lastErrorFile
     *
     * @return \BEAR\Package\Provide\Error\ErrorPage|ErrorPage
     */
    private function getErrorPage(\Exception $e, $lastErrorFile)
    {
        return PHP_SAPI === 'cli' ? new CliErrorPage($this->exceptionString->summery($e, $lastErrorFile)) : new ErrorPage;
    }

    /**
     * {@inheritdoc}
     */
    public function transfer()
    {
        $ro = $this->errorPage;
        $ro->code = $this->code;
        $ro->headers['content-type'] = 'application/vnd.error+json';
        $ro->body = $this->body;
        $this->responder->__invoke($ro, []);
    }

    /**
     * @param \Exception $e
     * @param Request    $request
     *
     * @return int logRef
     */
    private function log(\Exception $e, Request $request)
    {
        $logRefId = $this->getLogRefId($e);
        $logRefFile = sprintf('%s/e.%s.log', $this->logDir, $logRefId);
        $log = $this->exceptionString->detail($e, $request);
        file_put_contents($this->lastErrorFile, $log);
        file_put_contents($logRefFile, $log);

        return $logRefId;
    }

    /**
     * Return log ref id
     *
     * @param \Exception $e
     *
     * @return string
     */
    private function getLogRefId(\Exception $e)
    {
        return (string) crc32(get_class($e) . $e->getMessage() . $e->getFile() . $e->getLine());
    }
}
