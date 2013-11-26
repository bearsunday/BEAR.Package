<?php
return <<<EOT
<!DOCTYPE html>
<html lang="en" manifest="cache.manifest">
<head>
    <meta charset="utf-8">
    <title>BEAR.Package Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 60px;
        }
    </style>
</head>
<body>
    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
          <div class="container">
            <div class="navbar-header">
              <a class="navbar-brand" href="#">BEAR.Sunday Admin</a>
            </div>
          </div>
        </div>
    </div>

    <div class="container">
    {$contentsForLayout}
    </div>
</body>
</html>
EOT;
