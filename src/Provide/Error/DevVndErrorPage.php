<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Router\RouterMatch;

final class DevVndErrorPage extends ResourceObject
{
    public function __construct(\Exception $e, RouterMatch $request)
    {
        $status = new Status($e);
        $this->code = $status->code;
        $this->headers = $this->getHeader();
        $this->body = $this->getResponseBody($e, $request, $status);
    }

    public function toString()
    {
        $this->view = json_encode($this->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

        return $this->view;
    }

    private function getHeader() : array
    {
        return ['content-type' => 'application/vnd.error+json'];
    }

    private function getResponseBody(\Exception $e, RouterMatch $request, Status $status) : array
    {
        return [
            'message' => $status->text,
            'logref' => (string) new LogRef($e),
            'request' => (string) $request,
            'exceptions' => sprintf('%s(%s)', \get_class($e), $e->getMessage()),
            'file' => sprintf('%s(%s)', $e->getFile(), $e->getLine())
        ];
    }
}
