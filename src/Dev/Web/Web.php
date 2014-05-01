<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\Web;

use BEAR\Sunday\Extension\Application\AppInterface;

class Web
{
    /**
     * @var array
     */
    private $server;

    /**
     * @param array $server
     */
    public function __construct(array $server = [])
    {
        $this->server = $server ?: $_SERVER;
    }

    /**
     * @param string       $pagePath
     * @param AppInterface $app
     * @param string       $appDir
     *
     * @return int|string
     */
    public function service($pagePath, AppInterface $app, $appDir)
    {
        global $app;    // for template
        global $appDir; // for template

        $path = parse_url(substr($pagePath, 4))['path'];
        // + index.php
        if ($path == '' || substr($path, -1, 1) === '/') {
            $path .= 'index.php';
        }
        $packageRoot = dirname(dirname(dirname(__DIR__)));
        $scriptFile =  $packageRoot . '/var/www/dev/' . $path;
        if (file_exists($scriptFile) && is_file($scriptFile)) {
            /** @noinspection PhpIncludeInspection */
            ob_start();
            require $scriptFile;
            $html = ob_get_clean();
            return $html;
        }
        $scriptFile .= '.php';
        if (file_exists($scriptFile) && is_file($scriptFile)) {
            /** @noinspection PhpIncludeInspection */
            ob_start();
            include $scriptFile;
            $html = ob_get_clean();
            return $html;
        }
        echo "404";
        return 1;
    }

    /**
     * Return isDevWebService
     *
     * @param string $sapiName
     * @param string $requestUri
     *
     * @return bool
     */
    public function isDevWebService($sapiName, $requestUri)
    {
        $path = substr($requestUri, 0, 5);
        $isDevTool = ($sapiName !== 'cli') &&  ($path === '/dev' || $path === '/dev/');

        return $isDevTool;
    }
}
