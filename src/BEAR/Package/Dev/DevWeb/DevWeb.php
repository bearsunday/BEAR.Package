<?php
/**
* This file is part of the BEAR.Package package
*
* @package BEAR.Package
* @license http://opensource.org/licenses/bsd-license.php BSD
*/
namespace BEAR\Package\Dev\DevWeb;

use BEAR\Sunday\Extension\Application\AppInterface;

require dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/vendor/printo/printo/src.php';

/**
 * Dev web tools
 */
class DevWeb
{
    /**
     * Service dev web tool
     *
     * @param                                                 $pagePath
     * @param \BEAR\Sunday\Extension\Application\AppInterface $app
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
        if ($path == '' || substr($path, -1, 1) === '/'){
            $path .= 'index.php';
        }
        $scriptFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/docs/dev/public/' . $path;
        if (file_exists($scriptFile) && is_file($scriptFile)) {
            /** @noinspection PhpIncludeInspection */
            require $scriptFile;
            exit(0);
        }
        $scriptFile .= '.php';
        if (file_exists($scriptFile) && is_file($scriptFile)) {
            require $scriptFile;
            exit(0);
        }
        echo "404";
        exit(1);
    }
}
