<?php
echo "<i></i>"; // ??? reason unknown, doesn't work without output. plz PR you if got clue.
return <<<EOT
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>BEAR IDE</title>
    <base href="{$view['base']}" target="">
    <link href="//koriym.github.io/BEAR.Package/dev/ide/css/index.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="//koriym.github.io/BEAR.Package/dev/ide/jqueryFileTree/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="//koriym.github.io/BEAR.Package/dev/ide/css/edit.css" rel="stylesheet" type="text/css"  media="screen"/>
</head>

<body class="twoColHybLt" style="background-color:#DFE4EA">
<div style="background-color: #c9d9fb;">
    <img src="//koriym.github.io/BEAR.Package/dev/ide/jqueryFileTree/images/file.png" align="bottom">
    <span id="path" class="path">n/a</span>
</div>
<div id="container">
    <div id="sidebar1" style="overflow:scroll">
        <div id="container_id"></div>
    </div>
    <div id="mainContent">
        <div id="editor"
             style="position:absolute; left: 200px; ?>px; background-color:white; color:gray; width: 2000px; height: 95%; border: 1px solid black; "></div>
        <div id="file_info"></div>
        <div id="label" class="editor_label"><span class="editor_file_save" id="save_now"> n/a </span></div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script src="//d1n0x3qji82z53.cloudfront.net/src-min-noconflict/ace.js"></script>
        <script src="//koriym.github.io/BEAR.Package/dev/ide/js/codeEdit.js" type="text/javascript" charset="utf-8"></script>
        <script src="//koriym.github.io/BEAR.Package/dev/ide/js/jquery.easing.js" type="text/javascript"></script>
        <script src="js/init.js.php?root={$root}" type="text/javascript"></script>
        <script src="//koriym.github.io/BEAR.Package/dev/ide/jqueryFileTree/jqueryFileTree.js" type="text/javascript"></script>
        <script src="//koriym.github.io/BEAR.Package/dev/ide/js/index.js" type="text/javascript"></script>
</body>
</html>
EOT;
