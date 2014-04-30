<?php
/**
 * Resource list
 *
 * @global $app
 */
use BEAR\Package\Dev\Application\ApplicationReflector;

dependency: {
    $devDir = dirname(__DIR__);
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
        <th style="width:300px">METHODS</th>
        <th>LINKS</th>
    </tr>
</thead>
<tbody>
EOT;
    foreach ($resources as $uri => $resource) {
//        $uri = "<a href=\"item.php?uri={$uri}\">$uri</a>";
        $ref = new \ReflectionClass($resource['class']);
        $file = ($ref->implementsInterface('Ray\Aop\WeavedInterface')) ? $ref->getParentClass()->getFileName() : $ref->getFileName();
        $uri = "$uri <a href=\"../edit/?file={$file}\"><span class=\"glyphicon glyphicon-edit\"></span></span></a> <a href=\"graph.php?uri={$uri}\"><span class=\" glyphicon glyphicon-eye-open\"></span>";
        $buttonColor = [
            'get' => 'success btn-sm',
            'post' => 'danger btn-sm',
            'put' => 'warning btn-sm',
            'delete' => 'warning btn-sm',
        ];
        foreach ($resource['options']['allow'] as &$method) {
            $method = "<span class=\"btn btn-mini btn-{$buttonColor[$method]}\">{$method}</span>";
        }
        $options = implode(' ', ($resource['options']['allow']));
        $params = '';
        unset($resource['options']['allow']);
        $linkKeys = array_keys($resource['links']);
        $links = '';
        foreach ($linkKeys as $link) {
            $links .= '<span class="label label-default">' . $link . '</span> ';
        }
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
    <li><a href="/dev">Home</a></li>
    <li class="active">Resource</li>
    </ul>

    {$view['resource']}
    <a href="new" class="btn btn-default">
        <span class="glyphicon glyphicon-plus"></span> New Resource
    </a>
EOT;
    // two step view
    /** @noinspection PhpIncludeInspection */
    echo include $devDir . '/view/layout.php';
}
