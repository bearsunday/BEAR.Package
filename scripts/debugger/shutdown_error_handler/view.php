<?php
/**
 * @global $file
 * @global $line
 * @global $num
 */
functions: {
    /**
     * Return source
     *
     * @param     $file
     * @param     $line
     * @param int $num
     *
     * @return string
     */
    $getFile = function ($file, $line, $num = 6) {

        if (!file_exists($file) || $line === 0) {
            return '<pre>n/a</pre>';
        }
        $result = '<div class="file-summary">';
        $files = file($file);
        $fileArray = array_map('htmlspecialchars', $files);
        $hitLineOriginal = isset($fileArray[$line - 1]) ? $fileArray[$line - 1] : '';
        $fileArray[$line - 1] = "<span class=\"hit-line\">{$hitLineOriginal}</span>";
        $shortListArray = array_slice($fileArray, $line - $num, $num * 2);
        $shortListArray[$num - 1] = '<strog>' . $fileArray[$line - 1] . '</strong>';
        $shortList = implode('', $shortListArray);
        $shortList = '<pre class="short-list" style="background-color: #F0F0F9;">' . $shortList . '</pre>';
        $hitLine = $fileArray[$line - 1];
        $result .= $shortList . '</div>';

        return $result;
    };

    /**
     * Return linked trace list
     *
     * @param $stack
     *
     * @return string
     */
    $getTraceList = function ($stack) {
        $cnt = 0;
        $html = '';
        foreach ($stack as $key => $row) {
            $cnt++;
            foreach ($row as &$value) {
                if (is_object($value)) {
                    $value = get_class($value);
                }
            }
            $strValue = print_r($value, true);
            if (isset($row['file']) && is_file($row['file'])) {
                $html .= "<li>";
                $html .= "<a href=\"#\" class=\"\" data-toggle=\"collapse\" data-target=\"#args{$cnt}\"><i class=\"icon-zoom-in\"></i>";
                $html .= "<code>{$row['statement']}</code>";
                $html .= "</a>";
                $html .= "{$row['file']} : {$row['line']}  ";
                $html .= "<a target=\"code_edit\" href=\"/dev/edit/index.php?file={$row['file']}&line={$row['line']}\"><i class=\"icon-share-alt\"></i></a>";
                $html .= "</li>";
                $html .= "<div id=\"args{$cnt}\" class=\"collapse out\">{$row['source']}</div>";
            }
        }

        return $html;
    };

    $getStack = function ($xstack) use ($getFile) {
        $stack = [];
        $trace = array_slice(array_reverse($xstack), 1, -1);
        foreach ($trace as $row) {
            if (isset($row['class'])) {
                $row['type'] = isset($row['type']) && $row['type'] === 'dynamic' ? '->' : '::';
            }
            if (isset($row['params'])) {
                $row['args'] = $row['params'];
            }
            if (isset($row['class'])) {
                $row['statement'] = "{$row['class']}{$row['type']}{$row['function']}()";
            } elseif (isset($row['function'])) {
                $row['statement'] = "{$row['function']}()";
            } elseif (isset($row['include_filename'])) {
                $row['statement'] = "include_filename {$row['include_filename']}";
            } else {
                $row['statement'] = "...";
            }
            $row['source'] = isset($row['file']) ? $getFile($row['file'], $row['line']) : 'n/a';
            $stack[] = $row;
        }
        $ref = new \ReflectionProperty('Exception', 'trace');
        $ref->setAccessible(true);
        $exception = new \Exception;
        $ref->setValue($exception, $stack);

        return $stack;
    };
}

view_logic: {
    $xstack = (function_exists('xdebug_get_function_stack')) ? xdebug_get_function_stack() : debug_backtrace();
    $fileContents = htmlspecialchars(file_get_contents($file));
    $files = get_included_files();
    $includeFiles = $server = '';
    foreach (get_included_files() as $includeFile) {
        $includeFiles .= "<li><a target=\"code_edit\" href=\"/dev/edit/index.php?file={$includeFile}\">$includeFile</a></li>";
    }
    $includeFilesNum = count($files);
    foreach ($_SERVER as $key => $val) {
        $server .= "<b>$key</b>: $val<br>";
    }
    $lang = (function_exists('locale_accept_from_http')) ? locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']) : 'en';
    $errorNameFile = file_exists(__DIR__ . "/{$lang}/errors.php") ? __DIR__ . "/{$lang}/errors.php" : __DIR__ . "/en/errors.php";
    $errorNames = include $errorNameFile;
    $hitFile = $getFile($file, $line);
    $trace = $getTraceList($getStack($xstack));
    // misc
    $sec = number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 2);
    $memory = number_format(memory_get_peak_usage(true));
}

// output
return <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Error</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
	    .title {
	        font-size: 24px;
	        font-weight: bold;
	        line-height: 24px;
	    }
	    .weak {
	        color: gray;
	    }
    </style>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.1.1/css/bootstrap-combined.min.css" rel="stylesheet">
    <link href="/assets/js/google-code-prettify/prettify.css" type="text/css" rel="stylesheet" />
    <script src='http://cdnjs.cloudflare.com/ajax/libs/prettify/188.0.0/prettify.js' type='text/javascript'></script>
    <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <script src="//code.jquery.com/jquery-latest.js"></script>
    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.1.1/js/bootstrap.min.js"></script>
</head>

<body>

<div class="container">
    <div class="alert alert-block alert-error fade in">
        <a class="close" data-dismiss="alert" href="#">&times;</a>
    <div>
        <span class="title" class="alert-heading">{$errorNames[$type][0]}
            {$message}
        </span>
        <a class="brand" data-original-title="What's this ?" href="#" rel="popover" data-placement="bottom" data-content="{$errorNames[$type][1]}">
        [?]
        </a><br>
        in {$file} on line {$line}
        </div>
        <a class="btn" rel="tooltip" title="" href="/dev/edit/index.php?file={$file}&line={$line}">Edit</a></p>
    </div>
    <ul id="tab" class="nav nav-tabs">
        <li class="active"><a href="#summary" data-toggle="tab">Trace</a></li>
        <li><a href="#file" data-toggle="tab">File</a></li>
        <li><a href="#files" data-toggle="tab">Include Files</a></li>
        <li><a href="#server" data-toggle="tab">\$_SERVER</a></li>
    </ul>
    <div id="myTabContent" class="tab-content">
        <div class="tab-pane fade in active" id="summary">
            <a href="#" class="" data-toggle="collapse" data-target="#args"><span class="icon-fire"></span></a>{$file} : {$line}
            <div id="args" class="collapse out">{$hitFile}</div>
            {$trace}
        </div>

        <div class="tab-pane" id="file">
            <p><span class="icon-fire"></span><a target="code_edit" href="/dev/edit/index.php?file={$file}">{$file} : {$line}</a></P>
      <pre class="prettyprint linenums">
        {$fileContents}
      </pre>
        </div>
        <p></p>

        <div class="tab-pane" id="files">
            <p><span class="icon-file"></span>Files ({$includeFilesNum})</P>
            {$includeFiles}
        </div>

        <div class="tab-pane" id="server">
        <pre>{$server}</pre>
        </div>
        <footer>
            <hr>
            <span class="icon-time"></span> {$sec} sec
            <span class="icon-signal"></span> {$memory} bytes
        </footer>

    </div> <!-- /container -->

    <script>
        $(function () { prettyPrint() })
        $('a[rel=popover]').popover();
    </script>
    </body>
    </html>
EOT;
