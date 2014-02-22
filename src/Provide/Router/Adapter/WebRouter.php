<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router\Adapter;

class WebRouter implements AdapterInterface
{
    const METHOD_FILED = '_method';

    const METHOD_OVERRIDE_HEADER = 'HTTP_X_HTTP_METHOD_OVERRIDE';

    /**
     * @param string $path
     * @param array  $globals
     *
     * @return array [$method, $path, $query]
     */
    public function match($path, array $globals = [])
    {
        // $_POST['_method']
        if ($globals['_SERVER']['REQUEST_METHOD'] === 'POST' && isset($globals['_POST'][self::METHOD_FILED])) {
            return [
                strtolower($globals['_POST'][self::METHOD_FILED]),
                $path,
                $globals['_POST']
            ];
        }
        // $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']
        if ($globals['_SERVER']['REQUEST_METHOD'] === 'POST' && isset($globals['_SERVER'][self::METHOD_OVERRIDE_HEADER])) {
            return [
                strtolower($globals['_SERVER'][self::METHOD_OVERRIDE_HEADER]),
                $path,
                $globals['_POST']
            ];
        }
        // default
        return [
            strtolower($globals['_SERVER']['REQUEST_METHOD']),
            $path,
            $globals['_SERVER']['REQUEST_METHOD'] === 'GET' ? $globals['_GET'] : $globals['_POST']
        ];
    }
}
