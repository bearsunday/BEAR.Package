<?php
return <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{$view['app_name']} Dev</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="../../assets/css/bootstrap.css" rel="stylesheet">
    <style>
        body {
            padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
        }
    </style>
    <link href="../../assets/css/bootstrap-responsive.css" rel="stylesheet">
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <link rel="shortcut icon" href="../../assets/ico/favicon.png">
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
</div> <!-- /container -->
<!-- Le javascript
    ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="../../assets/js/jquery.js"></script>
<script src="../../assets/js/bootstrap-transition.js"></script>
<script src="../../assets/js/bootstrap-alert.js"></script>
<script src="../../assets/js/bootstrap-modal.js"></script>
<script src="../../assets/js/bootstrap-dropdown.js"></script>
<script src="../../assets/js/bootstrap-scrollspy.js"></script>
<script src="../../assets/js/bootstrap-tab.js"></script>
<script src="../../assets/js/bootstrap-tooltip.js"></script>
<script src="../../assets/js/bootstrap-popover.js"></script>
<script src="../../assets/js/bootstrap-button.js"></script>
<script src="../../assets/js/bootstrap-collapse.js"></script>
<script src="../../assets/js/bootstrap-carousel.js"></script>
<script src="../../assets/js/bootstrap-typeahead.js"></script>

</body>
</html>
EOT;
