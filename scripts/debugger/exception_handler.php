<?php
/**
 * Exception handler for development
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */

use BEAR\Package\Dev\Debug\ExceptionHandle\ExceptionHandler;
use Aura\Di\Exception;
use BEAR\Package\Provide\WebResponse\HttpFoundation as SymfonyResponse;
use BEAR\Package\Provide\ConsoleOutput\ConsoleOutput;

return function (\Exception $e) {
    $handler = new ExceptionHandler(
        new SymfonyResponse(new ConsoleOutput),
        dirname(dirname(__DIR__)) . '/src/BEAR/Package/Module/ExceptionHandle/template/view.php'
    );
    $handler->setLogDir(dirname(__DIR__) . '/data/log');
    $handler->handle($e);
};
