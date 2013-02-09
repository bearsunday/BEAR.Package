<?php
/**
* This file is part of the BEAR.Package package
*
* @package BEAR.Package
* @license http://opensource.org/licenses/bsd-license.php BSD
*/
namespace BEAR\Package\Dev\DevWeb;

use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Package\Provide\Application\AbstractApp;


require dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/vendor/printo/printo/src.php';

/**
 * Dev web tools
 */
class DevWeb
{
    /**
     * @param \BEAR\Package\Provide\Application\AbstractApp $app
     * @param                                               $pagePath
     *
     * @return void
     */
    public function service(AbstractApp $app, $pagePath)
    {
        global $rootDir;

        if (substr($pagePath, 0, 4) !== 'dev/') {
            return false;
        }
        $path = parse_url(substr($pagePath, 4))['path'];
        // + index.php
        if ($path == '' || substr($path, -1, 1) === '/'){
            $path .= 'index.php';
        }
        $appDir = dirname((new \ReflectionObject($app))->getFileName());
        $scriptFile = __DIR__ . '/web/' . $path;
        if (file_exists($scriptFile) && is_file($scriptFile)) {
            require $scriptFile;
            exit(0);
        }
        $scriptFile .= '.php';
        if (file_exists($scriptFile) && is_file($scriptFile)) {
            require $scriptFile;
            exit(0);
        }
    }
}
