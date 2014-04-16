<?php
/**
 * resource log list
 *
 * @global $app    \BEAR\Package\Provide\Application\AbstractApp
 * @global $appDir string
 */
use BEAR\Package\Dev\Resource\ResourceLog;

dependency: {
    $file = $appDir . '/var/log/resource.db';
    $cacheClear = isset($_GET['clear']);
}

control: {
    if ($cacheClear) {
        unlink($file);
        header('Location: /dev/log/index');
        exit;
    }
}

view: {
    $view['app_name'] = get_class($app);
    $view['log'] = (new ResourceLog($file))->toTable();
}
output: {
    $contentsForLayout = \DbugL::$css . <<<EOT
    <ul class="breadcrumb">
    <li><a href="/dev">Home</a> </li>
    <li class="active">Log</li>
    </ul>
    <p><a href="index"><span class="btn">Reload</span></a> <a href="index?clear"><span class="btn">Clear</span></a></p>
    {$view['log']}
EOT;
    echo include dirname(__DIR__) . '/view/layout.php';
}
