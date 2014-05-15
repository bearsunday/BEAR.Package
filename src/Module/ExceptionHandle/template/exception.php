<?php

use BEAR\Package\Dev\Debug\ExceptionHandle\Screen;

/**
 * @var \Exception $e
 * @var array      $view
 */
// @codingStandardsIgnoreStart
VIEW_LOGIC: {
    $screen = new Screen;
    $traceAsString = $screen->getTraceAsJsString($e->getTrace());
    $fileLink = $screen->getEditorLink($e->getFile(), $e->getLine());
    $file = $e->getFile();
    $line  = $e->getLine();
    $memory = number_format(memory_get_peak_usage(true));
    $files = get_included_files();
    $includeFiles = '';
    foreach ($files as $includeFile) {
        $includeFiles .= "<li><a target=\"code_edit\" href=\"/dev/edit/index.php?file={$includeFile}\">$includeFile</a></li>";
    }
    $includeFilesNum = count($files);
    $headers = $screen->getHeader($e, 'danger');
    $previousE = $e->getPrevious();
    if ($previousE) {
        $headers .= $screen->getHeader($previousE, 'warning');
    }
}
// @@codingStandardsIgnoreEnd
$html = <<<EOT
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Exception</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Le styles -->
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">
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
    <link rel="shortcut icon" href="/assets/ico/favicon.ico">
    <link href="/assets/js/google-code-prettify/prettify.css" type="text/css" rel="stylesheet" />
  </head>

  <body>
    <div class="container">
      {$headers}
      <iframe width="100%" height="400" src="/dev/edit/index.php?file={$file}&line={$line}"></iframe>

    <ul id="tab" class="nav nav-tabs">
      <li class="active"><a href="#summary" data-toggle="tab">Trace</a></li>
      <li><a href="#files" data-toggle="tab">Include Files</a></li>
    </ul>
    <div id="myTabContent" class="tab-content">
    <div class="tab-pane fade in active" id="summary">
      <p>{$traceAsString}</p>
    </div>

    <div class="tab-pane" id="files">
        <p><span class="icon-file"></span>Files ({$includeFilesNum})</P>
        {$includeFiles}
    </div>

    <div class="tab-pane" id="modules">
      <p><pre></pre></p>
    </div>


    <p></p>
      <footer>
        <hr>
        <span class="icon-signal"></span> {$memory} bytes
      </footer>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="//code.jquery.com/jquery-latest.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script>$(function () { prettyPrint() })</script>
</script>
  </body>
</html>
EOT;
return $html;
