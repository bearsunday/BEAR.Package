<?php
/**
 * Exception handler for development
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */

use BEAR\Package\Debug\ExceptionHandle\ExceptionHandler;
use Aura\Di\Exception;
use BEAR\Package\Web\SymfonyResponse;
use BEAR\Sunday\Output\Console;

return function (\Exception $e) {
    $handler = new ExceptionHandler(
        dirname(dirname(__DIR__)) . '/src/BEAR/Package/Module/ExceptionHandle/template/view.php',
        new SymfonyResponse(new Console)
    );
    $handler->setLogDir(dirname(__DIR__) . '/data/log');
    $handler->handle($e);
};