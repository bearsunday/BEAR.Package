<?php

use BEAR\Package\Dev\Debug\ExceptionHandle\Screen;

/**
 * @global $file
 * @global $line
 */

VIEW_LOGIC: {
    $screen = new Screen;
    $xStack = (function_exists('xdebug_get_function_stack')) ? xdebug_get_function_stack() : debug_backtrace();

    $traceAsString = $screen->getTraceAsJsString($xStack);
    $fileLink = $screen->getEditorLink($file, $line + 1);
    $sec = number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 2);
    $memory = number_format(memory_get_peak_usage(true));
    $files = get_included_files();
    $includeFiles = '';
    foreach ($files as $includeFile) {
        $includeFiles .= "<li><a target=\"code_edit\" href=\"/dev/edit/index.php?file={$includeFile}\">$includeFile</a></li>";
    }
    $includeFilesNum = count($files);
    $server = '';
    foreach ($_SERVER as $key => $val) {
        $server .= "<b>$key</b>: $val<br>";
    }
    $escapedOutputBuffer = nl2br(htmlspecialchars($outputBuffer));
}
// @@codingStandardsIgnoreEnd
$html = <<<EOT
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Fatal Error</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
      <div class="alert alert-block alert-danger fade in">
        <a class="close" data-dismiss="alert" href="#">&times;</a>
        <h2 class="alert-heading">Fatal Error</h2>
        <h3>{$message}</h3>
        <div>in {$file} on line {$line}</div>
      </div>
      <iframe width="100%" height="350" src="/dev/edit/index.php?file={$file}&line={$line}"></iframe>
      <p>
        {$traceAsString}
      </p>
    <ul id="tab" class="nav nav-tabs">
      <li class="active"><a href="#summary" data-toggle="tab">Output</a></li>
      <li><a href="#files" data-toggle="tab">Include Files</a></li>
      <li><a href="#server" data-toggle="tab">\$_SERVER</a></li>
    </ul>
    <div id="myTabContent" class="tab-content">
    <div class="tab-pane fade in active" id="summary">
      <p>{$escapedOutputBuffer}</p>
    </div>
    <div class="tab-pane" id="files">
        <p><span class="icon-file"></span>Files ({$includeFilesNum})</P>
        {$includeFiles}
    </div>
    <div class="tab-pane" id="server">
      <p>{$server}</p>
    </div>
      <footer>
            <hr>
        <span class="icon-time"></span> {$sec} sec
        <span class="icon-signal"></span> {$memory} bytes
      </footer>
    </div>
    <script src="//code.jquery.com/jquery-latest.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
  </body>
</html>
EOT;
return $html;
