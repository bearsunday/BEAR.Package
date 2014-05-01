<?php
namespace BEAR\Package\Dev\Web;

use BEAR\Package\Dev\Application\ApplicationReflector;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Annotations\Reader;

class Controller
{
    /**
     * @param Doctrine\Common\Annotations\Reader             $reader
     * @param BEAR\Sunday\Extension\Application\AppInterface $app
     *
     * @Ray\Di\Di\Inject
     */
    public function __construct(Reader $reader, AppInterface $app)
    {
        $this->reader = $reader;
        $this->app = $app;
    }

    public function onGet()
    {
        $resource = (new ApplicationReflector($this->app))->getResources()[$_GET['uri']];
        $ref = new \ReflectionClass($resource['class']);
    }
}

dependency: {
    $appDir = isset($_GLOBAL['_BEAR_APP_DIR']) ? $_GLOBAL['_BEAR_APP_DIR'] : dirname(dirname(dirname(__DIR__)));
}

control: {
    $mode = "Dev";
    $app = require $appDir . '/bootstrap/instance.php';
    /** @var $app \BEAR\Package\Provide\Application\AbstractApp */
    $appReflector = new ApplicationReflector($app);
    $resourceInfo = $appReflector->getResources()[$_GET['uri']];
    $ro = $app->resource->newInstance($_GET['uri']);
    $options = $appReflector->getResourceOptions($ro);

    exit;
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
        $uri = "<a href=\"item.php?file={$uri}\">$uri</a>";
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
    <li><a href="../index.php">Home</a></li>
    <li class="active">Resource</li>
    </ul>

    <h1>Resources</h1>
    {$view['resource']}
    <a href="new.php" class="btn btn-primary btn-large">New Resource</a>

EOT;
    // two step view
    echo include $devDir . '/view/layout.php';
}
