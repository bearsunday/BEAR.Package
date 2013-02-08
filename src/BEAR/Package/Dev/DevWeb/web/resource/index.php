<?php
/**
 * Resource list
 *
 * @global $app
 */
use BEAR\Package\Dev\Application\ApplicationReflector;

dependency: {
    $devDir = isset($_GLOBAL['_BEAR_DEV_DIR']) ? $_GLOBAL['_BEAR_DEV_DIR'] : dirname(__DIR__);
}

control: {
    $appReflector = new ApplicationReflector($app);
    $resources = $appReflector->getResources();
}

view: {
    $view['app_name'] = $appReflector->appName;
    $view['resource'] = <<<EOT
<table class="table table-hover table-condensed">
    <thead>
    <tr>
        <th>URI</th>
        <th>OPTIONS</th>
        <th>LINKS</th>
    </tr>
</thead>
<tbody>
EOT;
    foreach ($resources as $uri => $resource) {
//        $uri = "<a href=\"item.php?uri={$uri}\">$uri</a>";
        $file = (new \ReflectionClass($resource['class']))->getFileName();
        $uri = "$uri <a href=\"../edit/?file={$file}\"><span class=\"icon-edit\"></span></span></a> <a href=\"graph.php?uri={$uri}\"><span class=\" icon-eye-open\"></span>";
        $buttonColor = [
            'get' => 'success',
            'post' => 'danger',
            'put' => 'warning',
            'delete' => 'inverse',
        ];
        foreach ($resource['options']['allow'] as &$method) {
            $method = "<span class=\"btn btn-mini btn-{$buttonColor[$method]}\">{$method}</span>";
        }
        $options = implode(' ', ($resource['options']['allow']));
        $params = '';
        unset($resource['options']['allow']);
        $links = implode(', ', array_keys($resource['links']));
        foreach ($resource['options'] as $method => $param) {
            $param = "<span class=\"strong\">{$param}</span>";
            $params .= "<tr><td></td><td>{$method}: {$param}</td><td></td></tr>";
        }
        $view['resource'] .= <<<EOT
    <tr class=""><td><tt>{$uri}</tt></td><td>{$options}</td><td>{$links}</td></tr>
EOT;
//        $view['resource'] .= "<tr><td></td><td>{$params}</td></tr>";
    }
    $view['resource'] .= '</table>';
}
output: {
    // output
    $contentsForLayout = <<<EOT
    <ul class="breadcrumb">
    <li><a href="../">Home</a> <span class="divider">/</span></li>
    <li class="active">Resource</li>
    </ul>

    <h1>Resources</h1>
    {$view['resource']}
    <a href="new" class="btn btn-primary btn-large">New Resource</a>

EOT;
    // two step view
    /** @noinspection PhpIncludeInspection */
    echo include $devDir . '/view/layout.php';
}
