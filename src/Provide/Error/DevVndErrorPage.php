<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Router\RouterMatch;
use Throwable;

use function json_encode;
use function sprintf;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const PHP_EOL;

final class DevVndErrorPage extends ResourceObject
{
    public function __construct(Throwable $e, RouterMatch $request)
    {
        $status = new Status($e);
        $this->code = $status->code;
        $this->headers = $this->getHeader();
        $this->body = $this->getResponseBody($e, $request, $status);
    }

    public function toString(): string
    {
        $this->view = json_encode($this->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

        return $this->view;
    }

    /** @return array<string, string> */
    private function getHeader(): array
    {
        return ['content-type' => 'application/vnd.error+json'];
    }

    /** @return array<string, string> */
    private function getResponseBody(Throwable $e, RouterMatch $request, Status $status): array
    {
        return [
            'message' => $status->text,
            'logref' => (string) new LogRef($e),
            'request' => (string) $request,
            'exceptions' => sprintf('%s(%s)', $e::class, $e->getMessage()),
            'file' => sprintf('%s(%s)', $e->getFile(), $e->getLine()),
        ];
    }
}
