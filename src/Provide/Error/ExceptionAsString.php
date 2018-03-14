<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch as Request;

final class ExceptionAsString
{
    public function summery(\Exception $e, $log)
    {
        return sprintf("\n\n[%s]\n%s\n %s", \get_class($e), $e->getMessage(), $log);
    }

    public function detail(\Exception $e, Request $request) : string
    {
        $eSummery = sprintf(
            "%s(%s)\n in file %s on line %s\n\n%s",
            \get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        return sprintf("%s\n%s\n\n%s\n%s\n\n", date(DATE_RFC2822), $request, $eSummery, $this->getPhpVariables($_SERVER));
    }

    private function getPhpVariables(array $server) : string
    {
        if (PHP_SAPI === 'cli') {
            //            return '';
        }

        return sprintf("\nPHP Variables\n\n\$_SERVER => %s", print_r($server, true)); // @codeCoverageIgnore
    }
}
