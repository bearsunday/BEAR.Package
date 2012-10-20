<?php
/**
 * Sandbox
 *
 * @package App.Sandbox
 */

use Helloworld\Module\AppModule;
use Ray\Di\Injector;

$app = apc_fetch('app-helloworld');
if ($app) {
    return $app;
}

$injector = Injector::create([new AppModule], true);
$app = $injector->getInstance('BEAR\Sunday\Application\Context');

apc_store('app-helloworld', $app);
return $app;
