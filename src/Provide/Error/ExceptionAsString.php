<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch as Request;

class ExceptionAsString
{
    public function summery(\Exception $e, $log)
    {
        return sprintf("\n\n[%s]\n%s\n %s", get_class($e), $e->getMessage(), $log);
    }

    /**
     * @param \Exception $e
     * @param Request    $request
     *
     * @return string
     */
    public function detail(\Exception $e, Request $request)
    {
        $eSummery = sprintf(
            "[%s]\n%s\nin file %s on line %s\n\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        return sprintf("%s\n%s\n\n%s\n%s", date(DATE_RFC2822), $request, $eSummery, $this->getPhpVariables($_SERVER));
    }

    /**
     * @param array $server
     *
     * @return string
     */
    private function getPhpVariables(array $server)
    {
        if (PHP_SAPI === 'cli') {
            return '';
        }

        return sprintf("\nPHP Variables\n\n\$_SERVER => %s", print_r($server, true));
    }
}
