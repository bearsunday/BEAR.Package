<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch as Request;
use Stringable;
use Throwable;

use function date;
use function print_r;
use function sprintf;

use const DATE_RFC2822;

final class ExceptionAsString implements Stringable
{
    private string $string;

    public function __construct(Throwable $e, Request $request)
    {
        $eSummery = sprintf(
            "%s(%s)\n in file %s on line %s\n\n%s",
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString(),
        );

        /** @var array<string, string> $_SERVER */ //phpcs:ignore SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.NoAssignment
        $this->string = sprintf("%s\n%s\n\n%s\n%s\n\n", date(DATE_RFC2822), (string) $request, $eSummery, $this->getPhpVariables($_SERVER));
    }

    public function __toString(): string
    {
        return $this->string;
    }

    /** @param array<string, mixed> $server */
    private function getPhpVariables(array $server): string
    {
        return sprintf("\nPHP Variables\n\n\$_SERVER => %s", print_r($server, true)); // @codeCoverageIgnore
    }
}
