<?php
/**
 * resource log list
 *
 * @global $app    \BEAR\Package\Provide\Application\AbstractApp
 * @global $appDir string
 */
use BEAR\Package\Dev\Resource\ResourceLog;

dependency: {
    $file = $appDir . '/data/log/resource.db';
    $cacheClear = isset($_GET['clear']);
}

control: {
    if ($cacheClear) {
        unlink($file);
        header('Location: index');
    }
}

view: {
    $view['app_name'] = get_class($app);
    $view['log'] = (new ResourceLog($file))->toTable();
}
output: {
    $contentsForLayout = <<<EOT
    <ul class="breadcrumb">
    <li><a href="../">Home</a> <span class="divider">/</span></li>
    <li class="active">Log</li>
    </ul>
    <p><a href="index"><span class="btn">Reload</span></a> <a href="index?clear"><span class="btn">Clear</span></a></p>
    {$view['log']}
EOT;
    echo include dirname(__DIR__) . '/view/layout.php';
}
