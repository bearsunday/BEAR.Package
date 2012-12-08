<?php
/**
 * Exception handler for development
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */

use BEAR\Package\ExceptionHandle\ExceptionHandler;
use BEAR\Package\Web\SymfonyResponse;
use BEAR\Sunday\Output\Console;

return function ($e) {
    $handler = new ExceptionHandler(
        dirname(dirname(__DIR__))RR . '/src/BEAR/Package/Module/ExceptionHandle/template/exception.tpl.php',
        new SymfonyResponse(new Console)
    );
    $handler->setLogDir(dirname(__DIR__) . '/data/log');
    $handler->handle($e);
};