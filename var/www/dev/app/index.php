<?php

/**
 * @global $app
 */
use Koriym\Printo\Printo;

$a1 = $a2 = $a3 = '';
$disabled = ' class="disabled"';

if (isset($_GET['property'])) {
    $graph = (new Printo($app))->setRange(Printo::RANGE_PROPERTY)->setLinkDistance(130)->setCharge(-500);
    $a1 =$disabled;
} elseif (isset($_GET['full'])) {
    $graph = (new Printo($app))->setRange(Printo::RANGE_ALL);
    $a3 = $disabled;
} else {
    $graph = (new Printo($app))->setRange(0)->setCharge(-1800);
    $a1 = $disabled;
}

$view['app_name'] = get_class($app);
$contentsForLayout =<<<EOT
    <ul class="breadcrumb">
    <li><a href="/dev">Home</a> </li>
    <li class="active">Application</li>
    </ul>
    <ul class="nav nav-pills">
      <li{$a1}><a href="?">object</a></li>
      <li{$a2}><a href="?property">object + props</a></li>
      <li{$a3}><a href="?full">full</a></li>
    </ul>

    {$graph}
EOT;
echo include dirname(__DIR__) . '/view/layout.php';
