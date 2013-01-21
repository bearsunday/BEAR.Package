<?php
/**
 * Sandbox
 *
 * @package App.Sandbox
 */

use Helloworld\Module\AppModule;
use Ray\Di\Injector;

require_once __DIR__ . '/load.php';

$app = apc_fetch('app-helloworld');
if ($app) {
    return $app;
}

$injector = Injector::create([new AppModule]);
$app = $injector->getInstance('BEAR\Sunday\Application\AppInterface');

apc_store('app-helloworld', $app);
return $app;
