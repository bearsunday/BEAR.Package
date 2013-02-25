<?php

use BEAR\Package\Debug\ExceptionHandle\Screen;

/**
 * @var \Exception $e
 * @var array      $view
 */
view_logic: {
    $screen = new Screen;
    $traceAsString = $screen->getTraceAsJsString($e->getTrace());
    $fileLink = $screen->getEditorLink($e->getFile(), $e->getLine());
    $sec = number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 2);
    $memory = number_format(memory_get_peak_usage(true));
    $files = get_included_files();
    $includeFiles = '';
    foreach ($files as $includeFile) {
        $includeFiles .= "<li><a target=\"code_edit\" href=\"/dev/edit/index.php?file={$includeFile}\">$includeFile</a></li>";
    }
    $includeFilesNum = count($files);
    $file = htmlspecialchars(trim(file_get_contents($e->getFile())));
    $headers = $screen->getHeader($e, 'error');
    $previousE = $e->getPrevious();
    if ($previousE) {
        $headers .= $screen->getHeader($previousE, 'warning');
    }
    $dependencyBindings = $view['dependency_bindings'];
    $modules = print_r($view['modules'], true);
}
$html = <<<EOT
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Exception</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Le styles -->
    <link href="/assets/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
        body {
            padding-top: 20px;
            padding-bottom: 40px;
        }
        .hit-line {
            background-color: yellow;
            color: black;
            text-decoration: none;
        }
        .params {
            background-color: #eeeeee;
        }
        .params-table {
            font-size: 12px;
        }
        .weak {
            color: gray;
        }
    </style>
    <link href="/assets/css/bootstrap-responsive.css" rel="stylesheet">
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <link rel="shortcut icon" href="/assets/ico/favicon.ico">
    <link href="/assets/js/google-code-prettify/prettify.css" type="text/css" rel="stylesheet" />
  </head>

  <body>
    <div class="container">
      {$headers}
    <p></p>
    <ul id="tab" class="nav nav-tabs">
      <li class="active"><a href="#summary" data-toggle="tab">Trace</a></li>
      <li><a href="#file" data-toggle="tab">File</a></li>
      <li><a href="#files" data-toggle="tab">Include Files</a></li>
      <li><a href="#bindings" data-toggle="tab">Dependency Bindings</a></li>
      <li><a href="#modules" data-toggle="tab">Installed Modules</a></li>
    </ul>
    <div id="myTabContent" class="tab-content">
    <div class="tab-pane fade in active" id="summary">
    <p><span class="icon-fire"></span>{$fileLink}</P>
      <p>{$traceAsString}</p>
    </div>

    <div class="tab-pane" id="file">
      <p><span class="icon-fire"></span>{$fileLink}</P>
      <pre class="prettyprint linenums">
        {$file}
      </pre>
    </div>

    <div class="tab-pane" id="bindings">
      <p><pre>{$dependencyBindings}</pre></p>
    </div>

    <div class="tab-pane" id="files">
        <p><span class="icon-file"></span>Files ({$includeFilesNum})</P>
        {$includeFiles}
    </div>

    <div class="tab-pane" id="modules">
      <p><pre>{$modules}</pre></p>
    </div>


    <p></p>
      <footer>
        <hr>
        <span class="icon-time"></span> {$sec} sec
        <span class="icon-signal"></span> {$memory} bytes
      </footer>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/assets/js/jquery.js"></script>
    <script src="/assets/js/bootstrap-transition.js"></script>
    <script src="/assets/js/bootstrap-alert.js"></script>
    <script src="/assets/js/bootstrap-modal.js"></script>
    <script src="/assets/js/bootstrap-dropdown.js"></script>
    <script src="/assets/js/bootstrap-scrollspy.js"></script>
    <script src="/assets/js/bootstrap-tab.js"></script>
    <script src="/assets/js/bootstrap-tooltip.js"></script>
    <script src="/assets/js/bootstrap-popover.js"></script>
    <script src="/assets/js/bootstrap-button.js"></script>
    <script src="/assets/js/bootstrap-collapse.js"></script>
    <script src="/assets/js/bootstrap-carousel.js"></script>
    <script src="/assets/js/bootstrap-typeahead.js"></script>
    <script src="/assets/js/google-code-prettify/prettify.js"></script>
    <script>$(function () { prettyPrint() })</script>
</script>
  </body>
</html>
EOT;
return $html;
