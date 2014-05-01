<?php

/**
 * new resource
 *
 * @global $app \BEAR\Package\Provide\Application\AbstractApp
 */
use BEAR\Package\Dev\Application\ApplicationReflector;

dependency: {
    $devDir = dirname(__DIR__);
}

control: {
    $appReflector = new ApplicationReflector($app);
    $view['app_name'] = $appReflector->appName;
    $resources = $appReflector->getResources();
    if (! isset($_POST['uri'])) {
        $body = '';
        goto output;
    }
    // post
    try {
        list($filePath, $fileContents) = $appReflector->getNewResource($_POST['uri']);
        $appReflector->filePutContents($filePath, $fileContents);
        $code = 201;
        $status = 'Created';
    } catch (\Exception $e) {
        $code = $e->getCode();
        $status = $e->getMessage();
        $filePath = 'n/a';
        $fileContents = 'n/a';
    }
}

view: {
    $view['app_name'] = $appReflector->appName;
    $uri = $_POST['uri'];
    $contents = '<pre>' . highlight_string($fileContents, true) . '</pre>';
    $file = urlencode($filePath);
    $edit = ($code === 201) ? "<a href=\"../edit/?file={$file}\"><span class=\"btn\">Edit</span></a>" : '';
    $body = <<<EOT
    <hr>
    <h3>{$code} {$status}</h3>
    <p>{$edit}</p>
    <p>URI: <code>{$uri}</code></p>
    <p><tt>file: </tt><code>{$filePath}</code></p>
    <p>{$contents}</p>
EOT;
}

output: {
    $code = isset($code) ? $code : 200;
    http_response_code($code);
    // output
    $contentsForLayout = <<<EOT
    <ul class="breadcrumb">
    <li><a href="/dev">Home</a></li>
    <li><a href="index">Resource</a> </li>
    <li class="active">New</li>
    </ul>

    <form action="new.php" method="POST">
    <fieldset>
    <legend>Create a new resource</legend>
    <label>URI</label>
    <input type="text" class="input-xxlarge" name="uri" placeholder="page://self/index">
    <br>
    <button type="submit" class="btn">Submit</button>
    </fieldset>
    </form>
    {$body}
EOT;
    // two step view
    echo include $devDir . '/view/layout.php';
}
