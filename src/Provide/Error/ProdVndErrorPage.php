<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Router\RouterMatch;

final class ProdVndErrorPage extends ResourceObject
{
    public function __construct(\Exception $e, RouterMatch $request)
    {
        $status = new Status($e);
        $this->code = $status->code;
        $this->headers = $this->getHeader($status->code);
        $this->body = $this->getResponseBody($e, $request, $status);
    }

    public function toString()
    {
        $this->view = json_encode($this->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array
     */
    private function getHeader($code)
    {
        return ['content-type' => ($code >= 500) ? 'application/vnd.error+json' : 'application/json'];
    }

    /**
     * @param \Exception  $e
     * @param RouterMatch $request
     * @param int         $code
     *
     * @return array
     */
    private function getResponseBody(\Exception $e, RouterMatch $request, Status $status)
    {
        $body = ['message' => $status->text];
        if ($status->code >= 500) {
            $body['logref'] = (string) new LogRef($e);
        }

        return $body;
    }
}
