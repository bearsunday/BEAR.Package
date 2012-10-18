<?php
/**
 * Sandbox
 *
 * @package App.Sandbox
 */
namespace Helloworld;

use Helloworld\Module\AppModule;
use Ray\Di\Injector;

$injector = Injector::create([new AppModule], true);
$app = $injector->getInstance('BEAR\Sunday\Application\Context');

return $app;
