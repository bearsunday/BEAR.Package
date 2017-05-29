<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch as Request;

final class ExceptionToString
{
    /**
     * @param \Exception $e
     * @param Request    $request
     *
     * @return string
     */
    public function __invoke(\Exception $e, Request $request)
    {
        $date = date(DATE_RFC2822);
        $exceptions = $this->getExceptionString($e);
        $phpVal = $this->getPhpVariables($_SERVER);
        $trace = print_r($e->getTrace(), true);

        return <<<EOT
Request:

"{$request}" at {$date}

Exceptions:

{$exceptions}

PHP Variables:

{$phpVal}

Trace:

{$trace}

EOT;
        return sprintf("%s\n%s\n\n%s\nPHP Variables\n\n%sTrace:\n\n%s\n", date(DATE_RFC2822), $request, $eString, $this->getPhpVariables($_SERVER), $trace);
    }

    private function getExceptionString(\Exception $e, $string = '')
    {
        $string .= sprintf(
            "%s(%s) in file %s on line %s\n",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        $previous = $e->getPrevious();
        if ($previous instanceof \Exception) {
            $string = $this->getExceptionString($previous, $string);
        }

        return $string;
    }

    /**
     * @param array $server
     *
     * @return string
     */
    private function getPhpVariables(array $server)
    {
        return sprintf('$_SERVER => %s', print_r($server, true)); // @codeCoverageIgnore
    }
}
