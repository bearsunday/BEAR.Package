<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @global $app \BEAR\Sunday\Extension\Application\AppInterface
 *
 */

namespace BEAR\Package\Dev;

use Aura\Di\Exception;
use BEAR\Ace\ErrorEditor;
use BEAR\Package\Dev\Debug\ExceptionHandle\ExceptionHandler;
use BEAR\Package\Dev\Web\Web;
use BEAR\Package\Provide\Application\AbstractApp;
use BEAR\Package\Provide\ConsoleOutput\ConsoleOutput;
use BEAR\Package\Provide\WebResponse\HttpFoundation as SymfonyResponse;
use BEAR\Sunday\Extension\Application\AppInterface;

class Dev
{
    /**
     * @var AbstractApp
     */
    private $app;

    /**
     * @var string
     */
    private $appDir;

    /**
     * @var bool
     */
    private $return;

    /**
     * @var Web
     */
    private $web;

    /**
     * @var string
     */
    private $requestUri = '';

    /**
     * @var string
     */
    private $sapiName;

    /**
     * @param AppInterface $app
     * @param string       $appDir
     *
     * @return $this
     */
    public function setApp(AppInterface $app, $appDir)
    {
        $this->app = $app;
        $this->appDir = $appDir;

        return $this;
    }

    /**
     * @param array $server
     * @param null  $web
     * @param null  $sapiName
     */
    public function __construct(
        array $server = null,
        $web = null,
        $sapiName = null
    ) {
        global $argv;

        $this->web = $web ? : new Web;
        if (is_null($server) && isset($_SERVER)) {
            $server = $_SERVER;
        }
        if (isset($server['REQUEST_URI'])) {
            $this->requestUri = $server['REQUEST_URI'];
        } elseif (isset($argv[2])) {
            $this->requestUri = $argv[2];
        }
        $this->sapiName = $sapiName ? : php_sapi_name();
    }

    /**
     * Register profiler
     *
     * print [profile] link at the bottom of page if xhprof installed.
     *
     */
    public static function registerProfiler()
    {
        $enable = extension_loaded('xhprof') && (PHP_SAPI !== 'cli');
        if (!$enable) {
            return;
        }

        // ob start
        ob_start();

        // start
        xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);

        // stop
        register_shutdown_function(
            function () {
                $xhprof = xhprof_disable();
                if (!$xhprof) {
                    error_log('xhprof failed in ' . __FILE__);

                    return;
                }
                $id = (new XHProfRuns_Default)->save_run($xhprof, 'sunday');
                if ($id) {
                    $ob = ob_get_clean();
                    $replace = "<a style=\"position:absolute;right:20px; bottom:10px;\" class=\"btn btn btn-mini\" href=\"/xhprof_html/index.php?run={$id}&source=sunday\" target=\"_blank\">PROFILE</a></html>";
                    echo str_replace('</html>', $replace, $ob);
                }
            }
        );
    }

    /**
     * @return $this
     */
    public function iniSet()
    {
        umask(0);
        ini_set('display_errors', 0);
        ini_set('xhprof.output_dir', sys_get_temp_dir());
        ini_set('xdebug.collect_params', 0);
        ini_set('xdebug.max_nesting_level', 500);
        ini_set('xdebug.file_link_format', '/dev/edit/?file=%f&line=$l');

        return $this;
    }

    /**
     * @return $this
     */
    public function registerErrorHandler()
    {
        set_error_handler( function ($errNo, $errStr, $errFile, $errLine) {
            if (error_reporting() === 0) {

                // return in error-control operator(@)
                return;
            }
            throw new \ErrorException($errStr, 0, $errNo, $errFile, $errLine);
        });

        return $this;
    }

    /**
     * Register exception handler
     *
     * @param $logDir
     *
     * @return $this
     */
    public function registerExceptionHandler($logDir)
    {
        set_exception_handler(
            function (\Exception $e) use ($logDir) {
                $handler = new ExceptionHandler(
                    new SymfonyResponse(new ConsoleOutput),
                    (dirname(__DIR__)) . '/Module/ExceptionHandle/template/exception.php'
                );
                $handler->setLogDir($logDir);
                $handler->handle($e);
            }
        );

        return $this;
    }

    public function registerWhoopsErrorHandler()
    {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();

        return $this;
    }

    /**
     * Register syntax error editor
     *
     * @return $this
     */
    public function registerSyntaxErrorEdit()
    {
        (new ErrorEditor)->registerSyntaxErrorEdit();

        return $this;
    }

    /**
     * Register fatal error handler
     *
     * @return $this
     */
    public function registerFatalErrorHandler()
    {
        register_shutdown_function(
            function () {
                if (PHP_SAPI === 'cli') {
                    return;
                }
                $type = $message = $file = $line = $trace = '';
                $error = error_get_last();
                if (!$error) {
                    return;
                }
                extract($error);
                // Logic error only
                if (!in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR])) {
                    return;
                }

                // output error
                $outputBuffer = ob_get_clean();
                error_log($outputBuffer);
                http_response_code(500);
                $html = require __DIR__ . '/view/fatal_error.php';
                echo $html;
                exit(1);
            }
        );

        return $this;
    }

    /**
     * @param bool $return
     *
     * @return $this
     */
    public function setReturnMode($return = true)
    {
        $this->return = $return;

        return $this;
    }

    /**
     * Dev web service html
     *
     * @param string $requestUri
     *
     * @return string
     */
    public function getDevHtml($requestUri = null)
    {
        if (isset($this->app->router)) {
            $this->route($this->app);
        }
        if (! $this->web->isDevWebService($this->sapiName, $this->requestUri)) {
            return '';
        }
        $requestUri = $requestUri ? : $_SERVER['REQUEST_URI'];
        $html = $this->web->service($requestUri, $this->app, $this->appDir);

        return $html;
    }

    /**
     * @param array       $argv
     * @param AbstractApp $app
     */
    public function setCliArgs(array $argv, AbstractApp $app)
    {
        if ($argv && $this->sapiName === 'cli' && isset($argv)) {
            $app->router->setArgv($argv);
        }
    }

    /**
     * @return $this
     */
    public function loadDevFunctions()
    {
        error_log(__METHOD__  . ' is no longer necessary.');

        return $this;
    }

    /**
     * @param AbstractApp $app
     *
     * @return AbstractApp
     */
    private function route(AbstractApp $app)
    {
        global $argv;

        if (PHP_SAPI === 'cli') {
            // Use cli parameter for routing (web.php get /)
            $app->router->setArgv($argv);

            return $app;
        }

        return $app;
    }

    /*
     * @return string
     */
    private function getVendorDirectory()
    {
        $vendorDir = dirname(dirname(__DIR__)) . '/vendor';

        if (strpos(__DIR__, '/vendor/bear/package') !== false) {
            $baseDir = explode('/vendor/bear/package', __DIR__)[0];
            $vendorDir = $baseDir . '/vendor';
        }
        return $vendorDir;
    }
}
