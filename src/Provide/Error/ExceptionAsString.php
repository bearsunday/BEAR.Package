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
        $context = isset($GLOBALS['context']) ? "({$GLOBALS['context']})" . PHP_EOL : '';
        $phpVal = $this->getPhpVariables();
        $trace = $this->getTrace($e);

        return <<<EOT
{$date}
{$request} {$context}

Exceptions:

{$exceptions}
Trace:

{$trace}

PHP Variables:

{$phpVal}

EOT;

        return sprintf("%s\n%s\n\n%s\nPHP Variables\n\n%sTrace:\n\n%s\n", date(DATE_RFC2822), $request, $eString, $this->getPhpVariables($_SERVER), $trace);
    }

    private function getExceptionString(\Exception $e, $string = '')
    {
        $string .= sprintf(
            "%s(%s) in %s(%s)\n",
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
     * @return string
     */
    private function getPhpVariables()
    {
        $phpGlobals = [
            'GET' => $_GET,
            'POST' => $_POST,
            'COOKIE' => $_COOKIE,
            'FILES' => $_FILES,
            'SERVER' => $_SERVER,
            'app' => $GLOBALS['app']
        ];

        return print_r($phpGlobals, true);
    }

    private function getTrace(\Exception $e)
    {
        $trace = $e->getTrace();
        $i = 0;
        array_walk($trace, function (&$trace, $index) use ($i) {
            $trace = isset($trace['class']) ? $this->getClassTrace($trace, $index) : $this->getFunctionTrace($trace, $index);
        });

        return implode(PHP_EOL, $trace);
    }

    private function getClassTrace(array $trace, $index)
    {
        $string = sprintf('#%d %s%s%s(%s)', $index, $trace['class'], $trace['type'], $trace['function'], $this->argsAsString($trace['args']));

        return isset($trace['file']) ? $string . sprintf(' in %s(%s)', $trace['file'], $trace['line']) : $string;
    }

    private function getFunctionTrace(array $trace, $index)
    {
        return sprintf('#%d %s(%s) in %s(%s)', $index, $trace['function'], $this->argsAsString($trace['args']), $trace['file'], $trace['line']);
    }

    private function argsAsString(array $args)
    {
        foreach ($args as &$arg) {
            if (is_object($arg)) {
                $arg = $this->objectAsString($arg);
                continue;
            }
            if (is_array($arg)) {
                $arg = $this->argsAsString($arg);
                continue;
            }
            if (is_string($arg)) {
                $arg = sprintf("'%s'", $arg);
            }
        }

        return implode(',', $args);
    }

    private function objectAsString($object)
    {
        return sprintf('Object(%s)', get_class($object));
    }
}
