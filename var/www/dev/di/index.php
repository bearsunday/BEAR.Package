<?php
/**
 * @global $app \BEAR\Package\Provide\Application\AbstractApp
 */

$view['app_name'] = get_class($app);

$time = date('r', time());

$bindings = nl2br((string)$app->injector);
$bindings = preg_replace('/\w+:/', '<span style="background-color: #FFFFCC; font-weight:bold">$0</span>', $bindings);
$injections = nl2br((string)$app->injector->getLogger());
$injections = preg_replace('/\w+:/', '<span style="background-color: #FFFFCC; font-weight:bold">$0</span>', $injections);
$injections = preg_replace('/#singleton/', '<span style="color: gray; ">$0</span>', $injections);
$injections = preg_replace('/#prototype/', '<span style="color: red; ">$0</span>', $injections);
$contentsForLayout =<<<EOT
    <ul class="breadcrumb">
    <li><a href="../">Home</a> <span class="divider">/</span></li>
    <li class="active">Di log</li>
    </ul>
    <h2>Di log</h2>
    <h3>Bindings</h3>
    <div class="well">
    {$bindings}
    </div>
    <h3>Injection log</h3>
    <div class="well">
    {$injections}
    </div>
EOT;

echo include dirname(__DIR__) . '/view/layout.php';
