<?php
/**
 * Exception handler for development
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */

use BEAR\Sunday\Exception\ExceptionHandler;
use BEAR\Package\Web\SymfonyResponse;
use BEAR\Sunday\Output\Console;


return function ($e) {
    $handler = new ExceptionHandler(
        new SymfonyResponse(new Console),
        __DIR__ . '/template/exception.tpl.php'
    );
    $handler->setLogDir(dirname(__DIR__) . '/data/log');
    $handler->handle($e);
};