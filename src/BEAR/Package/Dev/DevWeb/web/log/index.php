<?php

$logger = $app->injector->getInstance('BEAR\Resource\LoggerInterface');
var_dump($logger);

/**
 * @global \BEAR\Package\Provide\Application\AbstractApp $app
 */
$view['app_name'] = get_class($app);
$view['log'] = <<<EOT
<table class="table table-hover table-condensed">
    <thead>
    <tr>
        <th>Time</th>
        <th>Request</th>
        <th>Status</th>
        <th>Result</th>
        <th>Aspect</th>
    </tr>
</thead>
<tbody>
EOT;

output: {
    $contentsForLayout =<<<EOT
    <ul class="breadcrumb">
    <li><a href="../">Home</a> <span class="divider">/</span></li>
    <li class="active">Log</li>
    </ul>

    <div class="well">
    <p><a href="index"><span class="btn">Reload</span></a></p>
    {$view['log']}
    </div>
EOT;
    echo include dirname(__DIR__) . '/view/layout.php';
}

