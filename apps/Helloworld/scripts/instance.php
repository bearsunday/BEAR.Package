<?php
/**
 * Sandbox
 *
 * @package App.Sandbox
 */

use Helloworld\Module\AppModule;
use Ray\Di\Injector;

require_once __DIR__ . '/load.php';

$hasApc = function_exists('apc_fetch');
$app = $hasApc && apc_fetch('app-helloworld');
if ($app) {
    return $app;
}

$injector = Injector::create([new AppModule]);
$app = $injector->getInstance('BEAR\Sunday\Extension\Application\AppInterface');

$hasApc ? apc_store('app-helloworld', $app): null;
return $app;
