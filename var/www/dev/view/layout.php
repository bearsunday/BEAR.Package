<?php
return <<<EOT
<!DOCTYPE html>
<html lang="en" manifest="cache.manifest">
<head>
    <meta charset="utf-8">
    <title>{$view['app_name']} Dev</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 60px;
        }
    </style>

    <link rel="shortcut icon" href="/favicon.png">
</head>

<body>
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="#">{$view['app_name']}</a>
        </div>
    </div>
</div>
<div class="container">
{$contentsForLayout}
</div>
<script src="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
</body>
</html>
EOT;
