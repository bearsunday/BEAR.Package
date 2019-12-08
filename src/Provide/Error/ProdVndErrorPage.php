<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Router\RouterMatch;

final class ProdVndErrorPage extends ResourceObject
{
    public function __construct(\Exception $e, RouterMatch $request)
    {
        unset($request);
        $status = new Status($e);
        $this->code = $status->code;
        $this->headers = $this->getHeader($status->code);
        $this->body = $this->getResponseBody($e, $status);
    }

    public function toString()
    {
        $this->view = json_encode($this->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

        return $this->view;
    }

    private function getHeader(int $code) : array
    {
        return ['content-type' => ($code >= 500) ? 'application/vnd.error+json' : 'application/json'];
    }

    private function getResponseBody(\Exception $e, Status $status) : array
    {
        $body = ['message' => $status->text];
        if ($status->code >= 500) {
            $body['logref'] = (string) new LogRef($e);
        }

        return $body;
    }
}
