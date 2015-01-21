<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Error;

use BEAR\Package\AbstractAppMeta;
use BEAR\Resource\Code;
use BEAR\Resource\Exception\BadRequestException as BadRequest;
use BEAR\Resource\Exception\ResourceNotFoundException as NotFound;
use BEAR\Resource\Exception\ServerErrorException;
use BEAR\Sunday\Extension\Error\ErrorInterface;
use BEAR\Sunday\Extension\Router\RouterMatch as Request;
use BEAR\Sunday\Provide\Error\ErrorPage;

/**
 * vnd.error for BEAR.Package
 *
 * @see https://github.com/blongden/vnd.error
 */
class VndError implements ErrorInterface
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $header = 'Content-Type: application/vnd.error+json';

    /**
     * @var array
     */
    private $body = ['message' => '', 'logref' => ''];

    public function __construct(AbstractAppMeta $appMeta)
    {
        $this->appMeta = $appMeta;
    }
    /**
     * {@inheritdoc}
     */
    public function handle(\Exception $e, Request $request)
    {
        $isCodeError = ($e instanceof NotFound || $e instanceof BadRequest || $e instanceof ServerErrorException);
        if ($isCodeError) {
            list($this->code, $this->body) = $this->codeError($e);

            return $this;
        }
        $this->code = 500;
        $this->body = ['message' => '500 Server Error'];

        error_log($request);
        error_log($e);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function transfer()
    {
        http_response_code($this->code);
        header($this->header);
        echo json_encode($this->body, JSON_PRETTY_PRINT);
    }

    /**
     * @param \Exception $e
     *
     * @return array [$code, $body]
     */
    private function codeError(\Exception $e)
    {
        $code = $e->getCode();
        $message =  $code . ' ' . (new Code)->statusText[$code];
        $body = ['message' => $message];

        return [$code, $body];
    }

    /**
     * @param \Exception $e
     *
     * @return string
     */
    private function logRef(\Exception $e)
    {
        $logRef = (string)CRC32($e);
        file_put_contents($this->appMeta->logDir . "/$logRef.log", $e);

        return $logRef;
    }
}
