<?php
/**
 * clear cache
 *
 * @global $app \BEAR\Package\Provide\Application\AbstractApp
 * @global $appDir string
 */

error_log('app files cleared by ' . __FILE__);
require $appDir . '/scripts/clear.php';


$view['app_name'] = get_class($app);
$time = date('r', time());
$contentsForLayout =<<<EOT
    <ul class="breadcrumb">
    <li><a href="../">Home</a> <span class="divider">/</span></li>
    <li class="active">Refresh</li>
    </ul>

    <div class="alert alert-info">
    <h3>Temporary items are cleared.</h3>
    <p>{$time}</p>
    <p><a href="index"><span class="btn">Clear Again</span></a></p>
    </div>
EOT;

echo include dirname(__DIR__) . '/view/layout.php';
