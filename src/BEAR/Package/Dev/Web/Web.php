<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\Web;

use BEAR\Sunday\Extension\Application\AppInterface;

/**
 * Dev web tools
 */
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
     * Service dev web tool
     *
     * @param string       $pagePath
     * @param AppInterface $app
     *
     * @return int exit code
     */
    public function service($pagePath, AppInterface $app = null)
    {
        // application directory path
        global $appDir;

        if ($app instanceof AppInterface) {
            $appDir = dirname((new \ReflectionObject($app))->getFileName());
        }
        $path = parse_url(substr($pagePath, 4))['path'];
        // + index.php
        if ($path == '' || substr($path, -1, 1) === '/') {
            $path .= 'index.php';
        }
        $scriptFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/docs/dev/public/' . $path;
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
            include $scriptFile;
            return 0;
        }
        echo "404";
        return 1;
    }

    /**
     * Return isDevWebService
     *
     * @param $sapiName
     * @param $requestUri
     *
     * @return bool
     */
    public function isDevWebService($sapiName, $requestUri)
    {
        $isDevTool = ($sapiName !== 'cli') && substr($requestUri, 0, 5) === '/dev/';

        return $isDevTool;
    }
}
