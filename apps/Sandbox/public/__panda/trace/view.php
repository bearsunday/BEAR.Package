<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Backtrace</title>

<base href="<?php echo "http://{$_SERVER['HTTP_HOST']}{$path}"; ?>__panda/" />
<link rel="shortcut icon" href="favicon.ico">
<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.0/themes/base/jquery-ui.css" rel="stylesheet" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7/jquery-ui.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/dojo/1.3.2/dojo/dojo.xd.js"></script>
<script type="text/javascript" src="/__panda/bespin/embed.js"></script>

<link rel="stylesheet" href="css/default.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/trace.css" type="text/css" media="screen">
<script type="text/javascript"><!--
    $(function(){
        // Tabs
        $('#tabs').tabs({ajaxOptions: { async : true, dataType : "html"}});
    });
    --></script>
</head>
<body onload="">
<h1>Backtrace</h1>
<div id="tabs">
<ul>
    <li><a href="#index">Summary</a></li>
    <?php
    for ($i = 0; $i < $levelNum; $i++) {
        echo "<li><a href=\"#tab-{$i}\">{$i}</a></li>";
    }
    ?>
    <li><a href="#raw">Raw</a></li>
</ul>

<div id="index">
<ol id="trace-summary" class="timeline">
<?php
echo $summaryPage;
?>
</ol>
</div>
<?php
for ($i = 0; $i < $levelNum; $i++) {
    $page = isset($tracePage[$i]) ? $tracePage[$i] : 'n/a';
    echo " <div id=\"tab-{$i}\">" . $page . "</div>";
}
?>
<div id="raw"><?php
echo $raw;
?></div>
</div>
</body>
</html>