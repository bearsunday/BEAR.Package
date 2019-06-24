<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch as Request;

final class ExceptionAsString
{
    /**
     * @var string
     */
    private $string;

    public function __construct(\Exception $e, Request $request)
    {
        $eSummery = sprintf(
            "%s(%s)\n in file %s on line %s\n\n%s",
            \get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        $this->string = sprintf("%s\n%s\n\n%s\n%s\n\n", date(DATE_RFC2822), (string) $request, $eSummery, $this->getPhpVariables($_SERVER));
    }

    public function __toString()
    {
        return $this->string;
    }

    private function getPhpVariables(array $server) : string
    {
        return sprintf("\nPHP Variables\n\n\$_SERVER => %s", print_r($server, true)); // @codeCoverageIgnore
    }
}
