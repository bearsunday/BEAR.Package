<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Package\Dev;

use Aura\Di\Exception;
use BEAR\Ace\ErrorEditor;
use BEAR\Package\Dev\Debug\ExceptionHandle\ExceptionHandler;
use BEAR\Package\Dev\Web\Web;
use BEAR\Package\Provide\Application\AbstractApp;
use BEAR\Package\Provide\ConsoleOutput\ConsoleOutput;
use BEAR\Package\Provide\WebResponse\HttpFoundation as SymfonyResponse;

/**
 * Dev tools
 */
class Dev
{
    /**
     * @var AbstractApp
     */
    private $app;

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
    private $requestUri;

    /**
     * @var string
     */
    private $sapiName;

    /**
     * Constructor
     *
     * @param array $server
     * @param null  $web
     * @param null  $sapiName
     *
     * @throws \BadMethodCallException
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
        } else {
            throw new \BadMethodCallException;
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

    public function iniSet()
    {
        ini_set('display_errors', 1);
        ini_set('xhprof.output_dir', sys_get_temp_dir());
        ini_set('xdebug.collect_params', 0);
        ini_set('xdebug.max_nesting_level', 500);
        ini_set('xdebug.var_display_max_depth', 1);
        ini_set('xdebug.file_link_format', '/dev/edit/?file=%f&line=$l');

        return $this;
    }

    /**
     * Register exception handler
     *
     * @param $logDir
     *
     * @return self
     */
    public function registerExceptionHandler($logDir)
    {
        set_exception_handler(
            function (\Exception $e) use ($logDir) {
                $handler = new ExceptionHandler(new SymfonyResponse(new ConsoleOutput), (dirname(
                        __DIR__
                    )) . '/Module/ExceptionHandle/template/view.php');
                $handler->setLogDir($logDir);
                $handler->handle($e);
            }
        );

        return $this;
    }

    /**
     * Register syntax error editor
     *
     * @return self
     */
    public function registerSyntaxErrorEdit()
    {
        (new ErrorEditor)->registerSyntaxErrorEdit();

        return $this;
    }

    /**
     * Register fatal error handler
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
                error_log(ob_get_clean());
                http_response_code(500);
                $html = require __DIR__ . '/view/fatal_error.php';
                echo $html;
                exit(1);
            }
        );

        return $this;
    }

    /**
     * @param AbstractApp $app
     *
     * @return $this
     */
    public function setApp(AbstractApp $app)
    {
        $this->app = $app;

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
     * @return bool false:has file, true:skip
     */
    public function directAccessFile()
    {
        $isDevWev = $this->web->isDevWebService($this->sapiName, $this->requestUri);
        if (!$isDevWev && php_sapi_name() == "cli-server") {
            $path = parse_url($this->requestUri)['path'];
            if (preg_match('/\.(?:png|jpg|jpeg|gif|js|css|ico|php|html)$/', $path)) {
                return false;
            }
            if (is_file(__DIR__ . preg_replace('#(\?.*)$#', '', $this->requestUri))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Web service
     *
     * @param null $requestUri
     *
     * @return array|bool
     */
    public function webService($requestUri = null)
    {
        if ($this->web->isDevWebService($this->sapiName, $this->requestUri)) {
            $requestUri = $requestUri ? : $_SERVER['REQUEST_URI'];
            $html = $this->web->service($requestUri, $this->app);

            return $this->output(200, $html);
        }

        return false;
    }

    /**
     * @param array $argv
     * @param       $app
     */
    public function setCliArgs(array $argv, $app)
    {
        if ($argv && $this->sapiName === 'cli' && isset($argv)) {
            $app->router->setArgv($argv);
        }
    }

    /**
     * Load debug function
     */
    public function loadDevFunctions()
    {
        require_once __DIR__ . '/function/e.php';
        require_once __DIR__ . '/function/p.php';

        return $this;
    }

    /**
     * @param $mode
     *
     * @return AbstractApp|bool
     */
    public function getDevApplication($mode)
    {
        global $argv;
        global $mode;

        // direct file for built in web server
        if ($this->directAccessFile() === false) {
            return false;
        }

        // console args
        /** @noinspection PhpUnusedLocalVariableInspection */
        $mode = isset($argv[3]) ? $argv[3] : $mode;
        $app = require 'scripts/instance.php';
        /** @var $app \BEAR\Package\Provide\Application\AbstractApp */

        // Use cli parameter for routing (web.php get /)
        if (PHP_SAPI === 'cli') {
            $app->router->setArgv($argv);
        } else {
            $app->router->setGlobals($GLOBALS);
            $argv = [];
        }

        // development web service (/dev)
        $this->setApp($app)->webService();

        // resource log
        $app->logger->register($app);

        return $app;
    }

    /**
     * Output
     *
     * @param $code
     * @param $html
     *
     * @return array
     */
    private function output($code, $html)
    {
        if ($this->return) {
            return [$code, $html];
        } else {
            http_response_code($code);
            echo $html;
            exit(0);
        }
    }
}
