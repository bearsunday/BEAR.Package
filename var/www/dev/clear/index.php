<?php
/**
 * clear cache
 *
 * @global $app \BEAR\Package\Provide\Application\AbstractApp
 * @global $appDir string
 */

error_log('app files cleared by ' . __FILE__);
require $appDir . '/bin/clear.php';


$view['app_name'] = get_class($app);
$time = date('r', time());
$contentsForLayout =<<<EOT
    <ul class="breadcrumb">
    <li><a href="/devs">Home</a> </li>
    <li class="active">Clear</li>
    </ul>

    <div class="alert alert-info">
    <h3>Temporary items are cleared.</h3>
    <p>{$time}</p>
    <p><a href="/dev/clear/"></p>
    </div>
    <span class="btn btn-default"><span class="glyphicon glyphicon-trash"></span> Retry</span></a>
EOT;

echo include dirname(__DIR__) . '/view/layout.php';
