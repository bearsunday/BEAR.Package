<?php
return <<<EOT
<!DOCTYPE html>
<html lang="en" manifest="cache.manifest">
<head>
    <meta charset="utf-8">
    <title>{$view['app_name']} Dev</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 60px;
        }
    </style>

    <link rel="shortcut icon" href="/dev/favicon.png">
</head>

<body>
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="navbar-brand" href="#">{$view['app_name']}</a>
        </div>
    </div>
</div>
<div class="container">
{$contentsForLayout}
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
</body>
</html>
EOT;
